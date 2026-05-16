# Chatting — Scalable Realtime Messaging Platform (Phase 1 Scaffold)

This repository contains the Phase 1 production-style scaffold for a private realtime messaging platform with modular frontend/backend architecture.

## Stack

- Frontend: React + Vite + Tailwind CSS + React Router + Axios + Socket.io Client + Zustand
- Backend: Node.js + Express + Socket.io + Prisma ORM
- Database: MySQL

## Project Structure

- `client/` — Web client application
- `server/` — API and realtime backend
- `server/prisma/schema.prisma` — Baseline scalable database schema

## Quick Start

### 1) Install dependencies

```bash
npm install
```

### 2) Configure environment

Copy `.env.example` to `.env` at repo root and set values.

### 3) Run development servers

```bash
npm run dev
```

- Client: `http://localhost:5173`
- Server API: `http://localhost:4000/api`
- Health: `http://localhost:4000/api/health`

### 4) Prisma client generation

```bash
npm run prisma:generate -w server
```

## Available Commands

- `npm run dev`
- `npm run build`
- `npm run lint`
- `npm run format`

## Notes

This commit provides a clean modular foundation (Phase 1). Auth, messaging business logic, admin policies, and full feature implementation are designed for subsequent phases.
