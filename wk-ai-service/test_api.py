#!/usr/bin/env python3
"""
Test script for WK AI Service
Run this to validate the API without needing GEMINI_API_KEY
"""

import requests
import json
from typing import Dict, Any

BASE_URL = "http://localhost:8000"

def test_health() -> bool:
    """Test health endpoint"""
    print("\n✅ Testing GET /health")
    try:
        response = requests.get(f"{BASE_URL}/health")
        print(f"Status: {response.status_code}")
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False


def test_root() -> bool:
    """Test root endpoint"""
    print("\n✅ Testing GET /")
    try:
        response = requests.get(f"{BASE_URL}/")
        print(f"Status: {response.status_code}")
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False


def test_analyze_opportunity() -> bool:
    """Test opportunity analysis endpoint"""
    print("\n✅ Testing POST /analyze")
    
    payload = {
        "id": "opp-001",
        "title": "Projeto de Transformação Digital - TechCorp",
        "description": "Implementação de ERP cloud e automação de processos",
        "value": 250000.00,
        "probability": 65,
        "status": "proposal",
        "customer_name": "TechCorp Brasil",
        "sector": "Tecnologia"
    }
    
    try:
        response = requests.post(
            f"{BASE_URL}/analyze",
            json=payload,
            headers={"Content-Type": "application/json"}
        )
        print(f"Status: {response.status_code}")
        result = response.json()
        print(f"Response: {json.dumps(result, indent=2, ensure_ascii=False)}")
        
        # Validate response structure
        required_fields = ["risk_score", "risk_label", "next_action", "recommendation", "summary"]
        missing = [f for f in required_fields if f not in result]
        if missing:
            print(f"❌ Missing fields: {missing}")
            return False
        
        print(f"✅ Risk Score: {result['risk_score']}")
        print(f"✅ Risk Label: {result['risk_label']}")
        return response.status_code == 200
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return False


def test_chat() -> bool:
    """Test chat endpoint"""
    print("\n✅ Testing POST /api/v1/chat")
    
    payload = {
        "question": "Qual é a melhor estratégia para aumentar a taxa de conversão de oportunidades?",
        "context": {
            "user_id": "user-123",
            "timestamp": "2026-01-12T10:30:00Z"
        }
    }
    
    try:
        response = requests.post(
            f"{BASE_URL}/api/v1/chat",
            json=payload,
            headers={"Content-Type": "application/json"}
        )
        print(f"Status: {response.status_code}")
        result = response.json()
        print(f"Response: {json.dumps(result, indent=2, ensure_ascii=False)}")
        return response.status_code == 200
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return False


def test_multiple_opportunities() -> bool:
    """Test analysis of multiple opportunities"""
    print("\n✅ Testing Multiple Opportunity Analyses")
    
    opportunities = [
        {
            "title": "Implementação SAP - Fortune 500",
            "value": 1000000,
            "probability": 85,
            "status": "negotiation",
            "sector": "Manufatura"
        },
        {
            "title": "Consultoria de Transformação Digital",
            "value": 50000,
            "probability": 40,
            "status": "open",
            "sector": "Varejo"
        },
        {
            "title": "Suporte técnico 24/7",
            "value": 15000,
            "probability": 95,
            "status": "proposal",
            "sector": "Serviços"
        }
    ]
    
    results = []
    for opp in opportunities:
        try:
            response = requests.post(
                f"{BASE_URL}/analyze",
                json=opp,
                headers={"Content-Type": "application/json"}
            )
            if response.status_code == 200:
                result = response.json()
                results.append({
                    "title": opp["title"],
                    "risk_score": result["risk_score"],
                    "risk_label": result["risk_label"]
                })
                print(f"✅ {opp['title']}: {result['risk_label']} (score: {result['risk_score']})")
            else:
                print(f"❌ {opp['title']}: HTTP {response.status_code}")
        except Exception as e:
            print(f"❌ {opp['title']}: {e}")
    
    return len(results) == len(opportunities)


def main():
    """Run all tests"""
    print("=" * 60)
    print("WK AI Service - Test Suite")
    print("=" * 60)
    
    tests = [
        ("Health Check", test_health),
        ("Root Endpoint", test_root),
        ("Opportunity Analysis", test_analyze_opportunity),
        ("Chat", test_chat),
        ("Multiple Opportunities", test_multiple_opportunities),
    ]
    
    results = []
    for test_name, test_func in tests:
        try:
            print(f"\n{'='*60}")
            print(f"Running: {test_name}")
            print('='*60)
            passed = test_func()
            results.append((test_name, "✅ PASSED" if passed else "❌ FAILED"))
        except Exception as e:
            print(f"❌ Exception: {e}")
            results.append((test_name, f"❌ FAILED: {e}"))
    
    # Summary
    print(f"\n\n{'='*60}")
    print("TEST SUMMARY")
    print('='*60)
    for test_name, result in results:
        print(f"{test_name}: {result}")
    
    passed_count = sum(1 for _, r in results if "✅" in r)
    total_count = len(results)
    print(f"\nTotal: {passed_count}/{total_count} tests passed")


if __name__ == "__main__":
    print("\nMake sure the WK AI Service is running on http://localhost:8000")
    print("Start it with: python main.py  (or uvicorn main:app --reload)")
    input("Press Enter to continue...")
    main()
