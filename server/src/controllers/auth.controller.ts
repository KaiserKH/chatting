import { Request, Response } from 'express';
import { clearAuthCookies, setAuthCookies } from '../utils/cookies.js';
import { loginSchema, registerSchema } from '../validations/auth.validation.js';
import { loginUser, registerUser, revokeAllSessions, revokeSession, rotateRefreshSession } from '../services/auth.service.js';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';

export async function register(request: Request, response: Response) {
  const payload = registerSchema.parse(request.body);
  const result = await registerUser({
    username: payload.username,
    phone: payload.phone,
    email: payload.email || null,
    password: payload.password,
    deviceName: payload.deviceName,
    ipAddress: String(request.ip ?? ''),
    userAgent: request.get('user-agent') ?? 'unknown'
  });

  setAuthCookies(response, result.accessToken, result.refreshToken);
  return ok(response, {
    accessToken: result.accessToken,
    user: {
      id: result.user.id,
      username: result.user.username,
      role: result.user.role.name
    }
  }, 'Registered', 201);
}

export async function login(request: Request, response: Response) {
  const payload = loginSchema.parse(request.body);
  const result = await loginUser({
    identifier: payload.identifier,
    password: payload.password,
    deviceName: payload.deviceName,
    ipAddress: String(request.ip ?? ''),
    userAgent: request.get('user-agent') ?? 'unknown'
  });

  setAuthCookies(response, result.accessToken, result.refreshToken);
  return ok(response, {
    accessToken: result.accessToken,
    user: {
      id: result.user.id,
      username: result.user.username,
      role: result.user.role.name
    }
  }, 'Logged in');
}

export async function refresh(request: Request, response: Response) {
  const refreshToken = request.signedCookies?.mp_refresh_token ?? request.cookies?.mp_refresh_token;
  if (!request.session || !refreshToken) {
    return response.status(401).json({ success: false, message: 'Unauthorized' });
  }

  const result = await rotateRefreshSession(request.session.id, refreshToken);
  setAuthCookies(response, result.accessToken, result.refreshToken);
  return ok(response, {
    accessToken: result.accessToken,
    user: {
      id: result.user.id,
      username: result.user.username,
      role: result.user.role.name
    }
  }, 'Token refreshed');
}

export async function logout(request: Request, response: Response) {
  if (request.session?.id) {
    await revokeSession(request.session.id);
  }
  clearAuthCookies(response);
  return ok(response, null, 'Logged out');
}

export async function me(request: Request, response: Response) {
  const user = await prisma.user.findUnique({
    where: { id: request.user?.id },
    include: { role: true, settings: true, sessions: { where: { revokedAt: null }, orderBy: { createdAt: 'desc' } } }
  });

  return ok(response, user, 'Current user');
}

export async function sessions(request: Request, response: Response) {
  const sessions = await prisma.userSession.findMany({
    where: { userId: request.user?.id },
    orderBy: { createdAt: 'desc' }
  });
  return ok(response, sessions, 'Sessions');
}

export async function revokeSessionController(request: Request, response: Response) {
  await revokeSession(String(request.params.id));
  return ok(response, null, 'Session revoked');
}

export async function revokeAllSessionsController(request: Request, response: Response) {
  await revokeAllSessions(request.user!.id, request.session?.id);
  clearAuthCookies(response);
  return ok(response, null, 'All sessions revoked');
}
