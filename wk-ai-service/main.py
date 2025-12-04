from fastapi import FastAPI

app = FastAPI()


@app.get("/health")
async def health():
    return {"status": "ok", "service": "wk-ai-service"}


@app.get("/")
async def root():
    return {"message": "WK AI Service - stub"}
