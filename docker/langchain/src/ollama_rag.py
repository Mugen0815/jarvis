from langchain_community.llms import Ollama
from langchain_community.document_loaders import WebBaseLoader
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain_community.embeddings import OllamaEmbeddings
from langchain_community.vectorstores import Chroma
from langchain.chains import RetrievalQA
from langchain.prompts import ChatPromptTemplate
import os
import shutil


CHROMA_PATH = "chromallamachain2"
OLLAMA_URL = "http://ollama:11434"
QUERY_TEXT = "Who is Neleus and who is in Neleus' family"

PROMPT_TEMPLATE = """
You are a helpful assistant.

---

Answer the question: {question}
"""
prompt_template = ChatPromptTemplate.from_template(PROMPT_TEMPLATE)
prompt = prompt_template.format( question=QUERY_TEXT)

ollama = Ollama(base_url=OLLAMA_URL, model="llama2")
print("Ollama loaded")

print(ollama.invoke(prompt))
#exit()

print("Loading data...")
loader = WebBaseLoader("https://www.gutenberg.org/files/1727/1727-h/1727-h.htm")
data = loader.load()
print("Data loaded")


print("Splitting data...")
text_splitter=RecursiveCharacterTextSplitter(chunk_size=500, chunk_overlap=0)
all_splits = text_splitter.split_documents(data)
print("Data split")


print("Creating embeddings...")
oembed = OllamaEmbeddings(base_url="http://ollama:11434", model="nomic-embed-text")
print("Embeddings created")

print("Creating vectorstore...")
if os.path.exists(CHROMA_PATH):
    shutil.rmtree(CHROMA_PATH)

vectorstore = Chroma.from_documents(documents=all_splits, embedding=oembed, persist_directory=CHROMA_PATH)
print("Vectorstore created")


question="Who is Neleus and who is in Neleus' family?"
docs = vectorstore.similarity_search(question)
print("Found", len(docs), "documents")
#len(docs)


print("Creating QA chain...")
qachain=RetrievalQA.from_chain_type(ollama, retriever=vectorstore.as_retriever())
print("QA chain created")

print(qachain.invoke({"query": question}))

print("Done")