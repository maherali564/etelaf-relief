# ADR-002: Payment Gateway Architecture

## Status
Accepted

## Context
The platform supports Stripe, PayPal, Wise, bank transfer, and cryptocurrency payments. Each has different APIs, webhook formats, and security requirements.

## Decision
Use a strategy pattern with a common PaymentService interface. Each gateway has its own service class (StripeService, PayPalService, WiseService) implementing the same contract. Webhook processing is centralized in WebhookController.

## Consequences
- Easy to add new payment gateways
- Consistent error handling across gateways
- WebhookController became large (394 lines) — needs refactoring
