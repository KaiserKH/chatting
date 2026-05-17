import { NextFunction, Request, Response } from 'express';
import { verifyAccessToken, verifyRefreshToken } from '../utils/tokens.js';
import { prisma } from '../config/prisma.js';
import { cookieNames } from '../config/constants.js';

export async function requireAuth(request: Request, response: Response, next: NextFunction) {
  const bearer = request.headers.authorization?.startsWith('Bearer ')
    ? request.headers.authorization.slice('Bearer '.length)
    : undefined;
  const token = bearer ?? request.signedCookies?.[cookieNames.accessToken] ?? request.cookies?.[cookieNames.accessToken];

  if (!token) {
    return response.status(401).json({ success: false, message: 'Unauthorized' });
  }

  try {
    const payload = verifyAccessToken(token);
    request.user = {
      id: payload.sub,
      role: payload.role as never,
      username: payload.username,
      sessionId: payload.sessionId
    };
    request.session = { id: payload.sessionId, userId: payload.sub };
    return next();
  } catch {
    return response.status(401).json({ success: false, message: 'Invalid access token' });
  }
}

export async function requireRefresh(request: Request, response: Response, next: NextFunction) {
  const token = request.signedCookies?.[cookieNames.refreshToken] ?? request.cookies?.[cookieNames.refreshToken];
  if (!token) {
    return response.status(401).json({ success: false, message: 'Refresh token missing' });
  }

  try {
    const payload = verifyRefreshToken(token);
    const session = await prisma.userSession.findUnique({ where: { id: payload.sessionId } });
    if (!session || session.revokedAt || session.expiresAt < new Date()) {
      return response.status(401).json({ success: false, message: 'Session expired' });
    }

    request.user = {
      id: payload.sub,
      role: payload.role as never,
      username: payload.username,
      sessionId: payload.sessionId
    };
    request.session = { id: payload.sessionId, userId: payload.sub };
    return next();
  } catch {
    return response.status(401).json({ success: false, message: 'Invalid refresh token' });
  }
}
