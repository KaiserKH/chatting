import { Router } from 'express';
import { login, logout, me, refresh, register, revokeAllSessionsController, revokeSessionController, sessions } from '../controllers/auth.controller.js';
import { requireAuth, requireRefresh } from '../middlewares/auth.js';
import { authLimiter } from '../middlewares/rateLimiters.js';
import { validateBody } from '../middlewares/validate.js';
import { loginSchema, registerSchema } from '../validations/auth.validation.js';

export const authRouter = Router();

authRouter.post('/register', authLimiter, validateBody(registerSchema), register);
authRouter.post('/login', authLimiter, validateBody(loginSchema), login);
authRouter.post('/refresh', requireRefresh, refresh);
authRouter.post('/logout', requireAuth, logout);
authRouter.get('/me', requireAuth, me);
authRouter.get('/sessions', requireAuth, sessions);
authRouter.delete('/sessions/:id', requireAuth, revokeSessionController);
authRouter.delete('/sessions', requireAuth, revokeAllSessionsController);
