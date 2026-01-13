# Secret Remediation Log

Date: 2026-01-10
Actor: GitHub Copilot (GPT-5)

## Summary
Accidental credentials discovered in documentation were sanitized to prevent exposure.

## Changes
- File: PROXIMOS-PASSOS-PRIORIDADES.en.md
  - Removed sensitive samples at the end of the document and replaced with placeholder tag `<REMOVED_SENSITIVE_SAMPLE>`.

## Notes
- Avoid embedding real passwords, tokens, or secrets in markdown files, scripts, or examples.
- Use environment variables (`.env`, GitHub Actions secrets) and redacted placeholders in docs.
- Consider rotating any credentials that may have been exposed publicly.

## Follow-ups
- Run a repository-wide secret scan before releases.
- Validate `.env` files are never committed for production.
