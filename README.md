# Chatting

Private scalable real-time messaging platform with React, Vite, Express, Socket.io, Prisma, and MySQL.

## Default Credentials

The repository ships with development defaults in [/.env.example](.env.example). Change all secrets before deploying to production.

Seeded super admin defaults:
- Username: `superadmin`
- Email: `admin@platform.local`
- Password: `Admin@123456!`
- Phone: `+10000000000`

These values are intentionally weak for local bootstrapping only and must be replaced in production.

## Project Layout

- `server/` Express, Socket.io, Prisma, auth, audit, and permissions layers
- `client/` Vite React app with route-based UI, Zustand, and Socket.io client
- `scripts/generate-secrets.js` generates a local `.env` with secure values

## Setup

1. Create your environment file:

   ```bash
   node scripts/generate-secrets.js
   ```

   Or copy [/.env.example](.env.example) to `.env` and fill in values manually.

2. Install dependencies:

   ```bash
   npm install
   ```

3. Prepare the database:

   ```bash
   npm run seed --workspace server
   ```

4. Start both apps:

   ```bash
   npm run dev
   ```

## How I would run it step by step

1. Generate secrets with `node scripts/generate-secrets.js`.
2. Review the emitted `.env` and replace any placeholder secrets.
3. Install dependencies with `npm install`.
4. Run Prisma generate and seed through the server workspace.
5. Start the API and client with `npm run dev`.
6. If a build or runtime error appears, fix the failing slice first, then rerun the same command.

## Implementation Log

- Ran `npm install` at the workspace root to install the server, client, and shared toolchain.
- Ran `npm run prisma:generate --workspace server` to validate the Prisma schema and generate the client.
- Ran `npm run build` to verify both workspaces compile together.
- Fixed Prisma relation validation errors in the schema by adding the missing opposite relations for conversation creation, media uploads, and moderation targets.
- Fixed route and typing issues in the server by narrowing Express params, normalizing request IP values, and returning auth tokens in the login and refresh responses.
- Fixed JWT helper overload issues by using runtime-safe casts around `jsonwebtoken` secrets and payloads.
- Fixed the client route guard typings by switching from `JSX.Element` to `ReactElement`.
- Refreshed dependencies after adding the Tailwind Vite plugin and updating the router version.
- Final verification command: `npm run build`.

## Deployment Notes

- Set `NODE_ENV=production`.
- Use HTTPS so `COOKIE_SECURE=true`.
- Point `CLIENT_URL` at the deployed frontend origin.
- Use MySQL 8+ with a managed backup strategy.
- Rotate all JWT and cookie secrets before production launch.

## Features Implemented

- JWT access and refresh token auth with HTTP-only cookies
- Session tracking and revocation-ready persistence
- Socket.io authenticated presence and messaging events
- Relationship-aware permissions and admin overrides
- Admin transparency logs for sensitive actions
- Prisma schema for core platform entities
- React/Vite dashboard, chat shell, settings, relationships, search, and admin pages
