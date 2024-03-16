#!/usr/bin/env python
"""Example LangChain server exposes a conversational retrieval chain.

Follow the reference here:

https://python.langchain.com/docs/expression_language/cookbook/retrieval#conversational-retrieval-chain

To run this example, you will need to install the following packages:
pip install langchain openai faiss-cpu tiktoken
"""  # noqa: F401


import os
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
from langchain.vectorstores.chroma import Chroma
from langchain_community.llms import Ollama
from langchain_community.embeddings import OllamaEmbeddings

from langchain_community.chat_models import ChatOpenAI
from langchain.chains import create_history_aware_retriever
from langchain import hub

CHROMA_PATH = "chromallama"
DATA_PATH = os.environ.get('DATA_PATH')
OPENAI_API_KEY = os.environ.get('OPENAI_API_KEY')
OLLAMA_URL ="http://ollama:11434"



rephrase_prompt = hub.pull("langchain-ai/chat-langchain-rephrase")
llm = Ollama(base_url=OLLAMA_URL, model="llama2")

embedding_function = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
db = Chroma(persist_directory=CHROMA_PATH, embedding_function=embedding_function)

retriever = db.as_retriever()

chat_retriever_chain = create_history_aware_retriever(
    llm, retriever, rephrase_prompt
)










app = FastAPI(
    title="LangChain Server",
    version="1.0",
    description="Spin up a simple api server using Langchain's Runnable interfaces",
)

@app.post("/v1/ragchat", include_in_schema=True)
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
    

    response_text = chat_retriever_chain.invoke(requestbody.get("input"))
    

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
    formatted_response_string = aResponse 
    
    return formatted_response_string







if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8000)