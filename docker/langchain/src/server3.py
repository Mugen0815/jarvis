

from operator import itemgetter
from typing import List, Tuple

from fastapi import Depends, FastAPI, Request, Response
from langchain_openai import ChatOpenAI
from langchain_openai import OpenAIEmbeddings
from langchain.prompts import ChatPromptTemplate
from langchain.prompts.prompt import PromptTemplate
from langchain.schema import format_document
from langchain.schema.output_parser import StrOutputParser
from langchain.schema.runnable import RunnableMap, RunnablePassthrough
from langchain_community.vectorstores import FAISS

from langserve import add_routes
from langserve.pydantic_v1 import BaseModel, Field
import os
from langchain.vectorstores.chroma import Chroma
from langchain_community.llms import Ollama
from langchain_community.embeddings import OllamaEmbeddings

CHROMA_PATH = "chromallama"
DATA_PATH = os.environ.get('DATA_PATH')
OPENAI_API_KEY = os.environ.get('OPENAI_API_KEY')
OLLAMA_URL ="http://ollama:11434"



_PROMPT_TEMPLATE_RAG = """
use this context to answer questions:

{context}

---

Answer the question based on the above context: {question}
"""

_PROMPT_TEMPLATE_RAG_ONLY = """
Answer the question based only on the following context:

{context}

---

Answer the question based on the above context: {question}
"""


PROMPT_TEMPLATE = _PROMPT_TEMPLATE_RAG




_TEMPLATE = """Given the following conversation and a follow up question, rephrase the 
follow up question to be a standalone question, in its original language.

Chat History:
{messages}
Follow Up Input: {question}
Standalone question:"""
CONDENSE_QUESTION_PROMPT = PromptTemplate.from_template(_TEMPLATE)

ANSWER_TEMPLATE = """Answer the question based only on the following context:
{context}

Question: {question}
"""
ANSWER_PROMPT = ChatPromptTemplate.from_template(ANSWER_TEMPLATE)

DEFAULT_DOCUMENT_PROMPT = PromptTemplate.from_template(template="{page_content}")


def _combine_documents(
    docs, document_prompt=DEFAULT_DOCUMENT_PROMPT, document_separator="\n\n"
):
    """Combine documents into a single string."""
    doc_strings = [format_document(doc, document_prompt) for doc in docs]
    return document_separator.join(doc_strings)


def _format_chat_history(chat_history: List[Tuple]) -> str:
    """Format chat history into a string."""
    buffer = ""
    for dialogue_turn in chat_history:
        print(dialogue_turn)
        #human = "Human: " + dialogue_turn[0]
        #ai = "Assistant: " + dialogue_turn[1]
        
        #buffer += "\n" + "\n".join([human, ai])
        if dialogue_turn['role'] == 'user':
            buffer += "\n" + "\n" + "User: "+ dialogue_turn['content']
        else:
            buffer += "\n" + "\n" + "Assistant: "+ dialogue_turn['content']
            
            
            
        #buffer += "\n" + "\n" + dialogue_turn['role'] +": "+ dialogue_turn['content']
    return buffer


vectorstore = FAISS.from_texts(
    ["harrison worked at kensho"], embedding=OpenAIEmbeddings(openai_api_key=OPENAI_API_KEY)
)
retriever2 = vectorstore.as_retriever()

embedding_function = OpenAIEmbeddings(openai_api_key=OPENAI_API_KEY)
db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)
retriever = db.as_retriever()


_inputs = RunnableMap(
    standalone_question=RunnablePassthrough.assign(
        chat_history=lambda x: _format_chat_history(x["messages"])
    )
    | CONDENSE_QUESTION_PROMPT
    | ChatOpenAI(openai_api_key=OPENAI_API_KEY,temperature=0)
    | StrOutputParser(),
)
_context = {
    "context": itemgetter("standalone_question") | retriever | _combine_documents,
    "question": lambda x: x["standalone_question"],
}

print(_context)

# User input
class ChatHistory(BaseModel):
    """Chat history with the bot."""

    chat_history: List[Tuple[str, str]] = Field(
        ...,
        extra={"widget": {"type": "chat", "input": "question"}},
    )
    question: str


conversational_qa_chain = (
    _inputs | _context | ANSWER_PROMPT | ChatOpenAI(openai_api_key=OPENAI_API_KEY) | StrOutputParser()
)
chain = conversational_qa_chain.with_types(input_type=ChatHistory)

ollama = Ollama(model="llama2",  base_url="http://ollama:11434")
#openai = ChatOpenAI(openai_api_key=OPENAI_API_KEY)
model = ollama
prompt = ChatPromptTemplate.from_template("Give me a summary about {topic} in a paragraph or less.")
chain2 = prompt | model
chain3 = ChatOpenAI(openai_api_key=OPENAI_API_KEY) | StrOutputParser()


app = FastAPI(
    title="LangChain Server",
    version="1.0",
    description="Spin up a simple api server using Langchain's Runnable interfaces",
)

add_routes(app, chain, path="/v2/chat", enable_feedback_endpoint=False) # results in


@app.post("/summerize", include_in_schema=False)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    topic = requestbody.get('topic')
    if topic:
        text_to_summarize = f"Give me a summary about {topic} in a paragraph or less."
        result = model.invoke(text_to_summarize)
    else:
        result = "No topic provided"
    
    return result


@app.post("/chat/completion", include_in_schema=False)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    # get last content in messages
    messages = requestbody.get('messages')
    content = messages[-1].get('content')
    
    
    if content:
        
        result = model.invoke(content)
    else:
        result = "Input invalid"
    
    return result

@app.post("/chat/chat", include_in_schema=False)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    # get last content in messages
    messages = requestbody.get('messages')
    content = messages[-1].get('content')
    question = content
    requestbody['question'] = question
    result = chain.invoke(requestbody)
    
    
    return result



@app.post("/rag", include_in_schema=False)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    # get last content in messages
    messages = requestbody.get('messages')
    content = messages[-1].get('content')
    QUERY_TEXT = content
    
    # Prepare the DB.
    embedding_function = OpenAIEmbeddings(openai_api_key=OPENAI_API_KEY)
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        return "Unable to find matching results."

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE)
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT)
    print(prompt)

    model = ChatOpenAI(openai_api_key=OPENAI_API_KEY)
    
    response_text = model.invoke(prompt)

    sources = [doc.metadata.get("source", None) for doc, _score in results]
    formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    return formatted_response




@app.post("/ragtest", include_in_schema=False)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    # get last content in messages
    messages = requestbody.get('messages')
    content = messages[-1].get('content')
    QUERY_TEXT = content
    
    # Prepare the DB.
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    #if len(results) == 0 or results[0][1] < 0.7:
        #print(f"Unable to find matching results.")
        #return "Unable to find matching results."

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE)
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT)
    print(prompt)

    model = Ollama(base_url=OLLAMA_URL, model="llama2")
    
    response_text = model.invoke(prompt)

    sources = [doc.metadata.get("source", None) for doc, _score in results]
    #formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    #formatted_response = requestbody
    
    #if(isset($aResponse['choices'][0]['message']['content']))
    aResponse = {
        'choices': [
            {
                'message': {
                    'role': 'assistant',
                    'content': response_text + "\nSources: " + str(sources)
                }
            }
        ]
    }
    #formatted_response['choices'].append({"message": {"role": "assistant", "content": response_text + "\nSources: " + str(sources)}})
    
    
    #new_message = {"role": "assistant", "content": response_text + "\nSources: " + str(sources)}
    #formatted_response['messages'].append(new_message)
    formatted_response_string = aResponse 
    return formatted_response_string


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8000)