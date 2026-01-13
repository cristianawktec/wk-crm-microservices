#!/bin/bash
# Script to start and test WK AI Service locally
# Usage: bash run-ai-service.sh

set -e

echo "🤖 WK AI Service - Start & Test Script"
echo "======================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo -e "${RED}❌ Python 3 not found!${NC}"
    echo "Please install Python 3.9+ first"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing dependencies${NC}"
pip install -r requirements.txt

echo -e "${YELLOW}Step 2: Setting up environment${NC}"
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
    echo -e "${YELLOW}⚠️  Please configure GEMINI_API_KEY in .env${NC}"
fi

echo -e "${YELLOW}Step 3: Starting FastAPI server${NC}"
echo "Server will run on http://localhost:8000"
echo "API docs available at http://localhost:8000/docs"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start with auto-reload
uvicorn main:app --reload --host 127.0.0.1 --port 8000
