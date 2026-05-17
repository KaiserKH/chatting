import express from 'express';
import http from 'node:http';
import { Server } from 'socket.io';
import helmet from 'helmet';
import cors from 'cors';
import cookieParser from 'cookie-parser';
import morgan from 'morgan';
import { env } from './config/env.js';
import { generalLimiter } from './middlewares/rateLimiters.js';
import { notFoundHandler, errorHandler } from './middlewares/errorHandler.js';
import { authRouter } from './routes/auth.routes.js';
import { chatRouter } from './routes/chat.routes.js';
import { friendRouter } from './routes/friend.routes.js';
import { searchRouter } from './routes/search.routes.js';
import { relationshipRouter } from './routes/relationship.routes.js';
import { settingsRouter } from './routes/settings.routes.js';
import { notificationRouter } from './routes/notification.routes.js';
import { adminRouter } from './routes/admin.routes.js';
import { registerSocketHandlers } from './sockets/index.js';

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: env.CLIENT_URL,
    credentials: true
  }
});

app.use(helmet());
app.use(cors({ origin: env.CLIENT_URL, credentials: true }));
app.use(express.json({ limit: '2mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser(env.COOKIE_SECRET));
app.use(morgan('dev'));
app.use(generalLimiter);

const apiPrefix = env.API_PREFIX;
app.get('/health', (_request, response) => response.json({ ok: true, service: 'chatting-api' }));
app.use(`${apiPrefix}/auth`, authRouter);
app.use(`${apiPrefix}/chat`, chatRouter);
app.use(`${apiPrefix}/friends`, friendRouter);
app.use(`${apiPrefix}/search`, searchRouter);
app.use(`${apiPrefix}/relationships`, relationshipRouter);
app.use(`${apiPrefix}/settings`, settingsRouter);
app.use(`${apiPrefix}/notifications`, notificationRouter);
app.use(`${apiPrefix}/admin`, adminRouter);

registerSocketHandlers(io);

app.use(notFoundHandler);
app.use(errorHandler);

server.listen(env.PORT, () => {
  console.log(`API listening on http://localhost:${env.PORT}${apiPrefix}`);
});
