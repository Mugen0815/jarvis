#!/usr/bin/env python
"""Example LangChain server exposes a conversational retrieval chain.

Follow the reference here:

https://python.langchain.com/docs/expression_language/cookbook/retrieval#conversational-retrieval-chain

To run this example, you will need to install the following packages:
pip install langchain openai faiss-cpu tiktoken
"""  # noqa: F401


from operator import itemgetter
from typing import List, Tuple

from fastapi import Depends, FastAPI, Request, Response
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
OLLAMA_MODEL = "calebfahlgren/natural-functions"

_TEMPLATE = """Given the following conversation and a follow up question, rephrase the 
follow up question to be a standalone question, in its original language.

Chat History:
{chat_history}
Follow Up Input: {question}
Standalone question:"""
CONDENSE_QUESTION_PROMPT = PromptTemplate.from_template(_TEMPLATE)

ANSWER_TEMPLATE = """Answer the question based only on the following context:
{context}

Question: {question}
"""
ANSWER_PROMPT = ChatPromptTemplate.from_template(ANSWER_TEMPLATE)

DEFAULT_DOCUMENT_PROMPT = PromptTemplate.from_template(template="{page_content}")


PROMPT_TEMPLATE_ORG = """
Answer the question based only on the following context:

{context}

---

Answer the question based on the above context: {question}
"""


PROMPT_TEMPLATE_V2 = """
You are a helpful assistant. Your name is Jarvis.
Answer the question. Use the following context, if it has any relevant information for the given question:

{context}

---

Answer the question, using the above context, if it has any relevant information for the given question: {question}
"""


PROMPT_TEMPLATE_V3 = """
You are a helpful assistant, that is having a conversation with a user. You can follow up on the conversation, and ask questions to the user. Your name is Jarvis.
Answer the question. Use the following context, if it has any relevant information for the given question:
>>>BEGIN_CONTEXT<<<
{context}
>>>END_CONTEXT<<<
>>>BEGIN_CONVERSATION_HISTORY<<<
{messages}
>>>END_CONVERSATION_HISTORY<<<
---

Answer the question, using the above context, if it has any relevant information for the given question: {question}
"""




PROMPT_TEMPLATE_V4 = """
You are a helpful assistant, that is having a conversation with a user. You can follow up on the conversation, and ask questions to the user. Your name is Jarvis.
Kepp your answers short and to the point.
Use the provided context, if it has any relevant information for answering the given question.
You are able to make function calls.
The following functions are available for you to fetch further data to answer user questions, if relevant:
>>>BEGIN_FUNCTIONS<<<
{system_message}
>>>END_FUNCTIONS<<<
To call one or more functions, respond - immediately and only - with a JSON array of the following format:
[
    {{
        "function": "function_name",
        "arguments": {{
            "argument1": value1,
            "argument2": value2
        }}
    }}
]
When calling a function, all output must be in valid JSON
>>>BEGIN_CONTEXT<<<
{context}
>>>END_CONTEXT<<<
>>>BEGIN_CONVERSATION_HISTORY<<<
{messages}
>>>END_CONVERSATION_HISTORY<<<
---

Answer the question, using the above context, if it has any relevant information for the given question: {question}
"""



PROMPT_TEMPLATE_V5 = """
You are a helpful assistant, that is having a conversation with a user. You can follow up on the conversation, and ask questions to the user. Your name is Jarvis.
Kepp your answers short and to the point.
Use the provided context, if it has any relevant information for answering the given question.
You are able to make function calls.
>>>BEGIN_CONTEXT<<<
{context}
>>>END_CONTEXT<<<
>>>BEGIN_CONVERSATION_HISTORY<<<
{messages}
>>>END_CONVERSATION_HISTORY<<<
The following functions are available for you to fetch further data to answer user questions, if relevant:
>>>BEGIN_FUNCTIONS<<<
{system_message}
>>>END_FUNCTIONS<<<
To call one or more functions, respond - immediately and only - with a JSON array of the following format:
[
    {{
        "function": "function_name",
        "arguments": {{
            "argument1": value1,
            "argument2": value2
        }}
    }}
]
When calling a function, all output must be in valid JSON
---

Answer the question or make a function call: {question}
"""


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
        human = "Human: " + dialogue_turn[0]
        ai = "Assistant: " + dialogue_turn[1]
        buffer += "\n" + "\n".join([human, ai])
    return buffer



embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

retriever = db.as_retriever()

_inputs = RunnableMap(
    standalone_question=RunnablePassthrough.assign(
        chat_history=lambda x: _format_chat_history(x["chat_history"])
    )
    | CONDENSE_QUESTION_PROMPT
    | Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL)
    | StrOutputParser(),
)
_context = {
    "context": itemgetter("standalone_question") | retriever | _combine_documents,
    "question": lambda x: x["standalone_question"],
}


# User input
class ChatHistory(BaseModel):
    """Chat history with the bot."""

    chat_history: List[Tuple[str, str]] = Field(
        ...,
        extra={"widget": {"type": "chat", "input": "question"}},
    )
    question: str


conversational_qa_chain = (
    _inputs | _context | ANSWER_PROMPT | Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL) | StrOutputParser()
)
chain1 = conversational_qa_chain.with_types(input_type=ChatHistory)

app = FastAPI(
    title="LangChain Server",
    version="1.0",
    description="Spin up a simple api server using Langchain's Runnable interfaces",
)

#add_routes(app, chain, enable_feedback_endpoint=True)



def convert_messages_to_chat_history(messages):
    #if there is a first message with role system, remove it
    messages = [message for message in messages if message['role'] != 'system']
    chat_history = [[message['content'] for message in messages]]
    #remove last item
    #chat_history = chat_history[0:-1]
    
    return {'chat_history': chat_history}


def enrich_request(requestbody):
    
    QUESTION = ""
    
    if 'input' in requestbody:
        if 'question' in requestbody['input']:
            QUESTION = requestbody['input']['question']
        
    if 'messages' in requestbody:
        if len(requestbody['messages']) > 0:
            QUESTION = requestbody['messages'][-1]['content']
        if len(requestbody['messages']) > 1:    
            chathistory = convert_messages_to_chat_history(requestbody['messages'])
            print("Chat history:")
            print(chathistory)
        else:
            chathistory = []    
    
    if not 'input' in requestbody:
        requestbody['input'] = {'question': QUESTION}
        
    if 'input' in requestbody and not 'chat_history' in requestbody['input']:
        requestbody['input']['chat_history'] = chathistory
        
    
    return requestbody

def create_json_response(response_text):
    aResponse = {
        'choices': [
            {
                'message': {
                    'role': 'assistant',
                    'content': response_text
                }
            }
        ]
    }
    return aResponse

def normalize_scores(scores):
    min_score = min(scores)
    max_score = max(scores)
    return [(score - min_score) / (max_score - min_score) for score in scores]






@app.post("/ragchat/v1", include_in_schema=True)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    requestbody = enrich_request(requestbody)
    QUERY_TEXT = requestbody['input']['question']
    
    
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    print("Results:")
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        #return "Unable to find matching results."
    else:
        print(results[0][0])

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    sources = [doc.metadata.get('source') + ":" + str(doc.metadata.get('start_index')) for doc, _score in results]
    print(requestbody)
    
    response_text = chain1.invoke(requestbody.get("input"))
    response_text += "\nSources: " + str(sources)
    formatted_response_string = create_json_response(response_text) 
    
    return formatted_response_string



@app.post("/ragchat/v2", include_in_schema=True)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    requestbody = enrich_request(requestbody)
    QUERY_TEXT = requestbody['input']['question']
  
    
    # Prepare the DB.
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        #return

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE_BCK)
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT)
    print(prompt)

    model = Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL)
    #response_text = model.predict(prompt)
    response_text = model.invoke(prompt)

    sources = [doc.metadata.get("source", None) for doc, _score in results]
    formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    formatted_response_string = create_json_response(response_text)
    
    return formatted_response_string



@app.post("/ragchat/v3", include_in_schema=True)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    requestbody = enrich_request(requestbody)
    QUERY_TEXT = requestbody['input']['question']
    messages = requestbody['messages']
    messages = [message for message in messages if message['role'] != 'system']
    
    # Prepare the DB.
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        #return

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE_V3)
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT, messages=messages)
    print(prompt)

    model = Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL)
    #response_text = model.predict(prompt)
    response_text = model.invoke(prompt)

    sources = [doc.metadata.get("source", None) for doc, _score in results]
    formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    formatted_response_string = create_json_response(response_text)
    
    return formatted_response_string




@app.post("/ragchat/v4", include_in_schema=True)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    requestbody = enrich_request(requestbody)
    QUERY_TEXT = requestbody['input']['question']
    messages = requestbody['messages']
    systemmessages = [message for message in messages if message['role'] == 'system']
    messages = [message for message in messages if message['role'] != 'system']
    
    # Prepare the DB.
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        #return

    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE_V4)
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT, messages=messages , system_message=systemmessages[0]['content'])
    print(prompt)

    model = Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL)
    #response_text = model.predict(prompt)
    response_text = model.invoke(prompt)

    sources = [doc.metadata.get("source", None) for doc, _score in results]
    formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    formatted_response_string = create_json_response(response_text)
    
    return formatted_response_string




@app.post("/ragchat/v5", include_in_schema=True)
async def simple_invoke(request: Request) -> Response:
    """Handle a request."""
    requestbody = await request.json()
    
    requestbody = enrich_request(requestbody)
    QUERY_TEXT = requestbody['input']['question']
    messages = requestbody['messages']
    systemmessages = [message for message in messages if message['role'] == 'system']
    messages = [message for message in messages if message['role'] != 'system']
    
    # Prepare the DB.
    embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
    db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

    # Search the DB.
    results = db.similarity_search_with_relevance_scores(QUERY_TEXT, k=3)
    documents, scores = zip(*results)
    normalized_scores = normalize_scores(scores)
    results = list(zip(documents, normalized_scores))
    if len(results) == 0 or results[0][1] < 0.7:
        print(f"Unable to find matching results.")
        context_text = ""
        #return
    else:
        context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
        
    context_text = "\n\n---\n\n".join([doc.page_content for doc, _score in results])
    
    
    prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE_V5)
    #remove last message from messages
    messages = messages[0:-1]    
    
    prompt = prompt_template.format(context=context_text, question=QUERY_TEXT, messages=messages , system_message=systemmessages[0]['content'])
    print(prompt)

    model = Ollama(base_url=OLLAMA_URL, model=OLLAMA_MODEL, temperature=0.2, num_ctx=4096, repeat_penalty=1.2)
    #response_text = model.predict(prompt)
    response_text = model.invoke(prompt)
    print(response_text)
    #remove all <|im_end|> from response_text
    response_text = response_text.replace("<|im_end|>", "")
    response_text = response_text.replace(">>>END_CONVERSATION<<<", "")
    print(response_text)
    
    sources = [doc.metadata.get("source", None) for doc, _score in results]
    sources = ""
    formatted_response = f"Response: {response_text}\nSources: {sources}"
    
    formatted_response_string = create_json_response(response_text)
    
    return formatted_response_string






if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8000)