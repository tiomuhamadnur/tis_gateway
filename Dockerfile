FROM python:3.11-slim AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libc6-dev \
    libjpeg-dev \
    zlib1g-dev \
    libfreetype-dev \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir --target=/deps -r requirements.txt

FROM python:3.11-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    iputils-ping \
    ca-certificates \
    libjpeg62-turbo \
    zlib1g \
    libfreetype6 \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /deps /deps
ENV PYTHONPATH=/deps

WORKDIR /app
COPY . .

VOLUME ["/app/output", "/app/logs"]

ENTRYPOINT ["python", "main.py"]
