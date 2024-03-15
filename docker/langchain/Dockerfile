ARG IMAGE=python:3.11.8
ARG CMAKE_ARGS="-DLLAMA_BLAS=ON -DLLAMA_BLAS_VENDOR=OpenBLAS -DLLAMA_AVX=OFF -DLLAMA_AVX2=OFF -DLLAMA_F16C=OFF -DLLAMA_FMA=OFF"

# App-Stage
FROM ${IMAGE}
ARG IMAGE

LABEL maintainer="Mugen0815 <mugen0815@gmail.com>"
LABEL description="Docker container for simple-rag"

WORKDIR /app

# Install dependencies
RUN apt-get update
RUN apt-get install ffmpeg libsm6 libxext6  -y

# Setup simple-rag
RUN pip install --upgrade pip \
       langchain \
       langchain-community \ 
       langchain-openai \ 
       unstructured \ 
       chromadb \ 
       openai \ 
       tiktoken \ 
       pygments \
       "unstructured[pdf]" \
       faiss-cpu \
       sse_starlette \
       langserve


# Setup environment
ENV TZ="UTC" 

COPY src/ /app/
RUN ls --recursive /app/


# Setup entrypoint
#COPY docker-entrypoint.sh /
#RUN chmod +x /docker-entrypoint.sh
CMD ["python3"]