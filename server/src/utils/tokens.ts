import jwt from 'jsonwebtoken';
import { env } from '../config/env.js';

type JwtSecret = Parameters<typeof jwt.sign>[1];
type JwtExpiresIn = Exclude<Parameters<typeof jwt.sign>[2], undefined>['expiresIn'];

export type TokenPayload = {
  sub: string;
  sessionId: string;
  role: string;
  username: string;
};

export function signAccessToken(payload: TokenPayload): string {
    return jwt.sign(payload as any, env.JWT_ACCESS_SECRET as any, { expiresIn: env.JWT_ACCESS_EXPIRES_IN as any });
}

export function signRefreshToken(payload: TokenPayload): string {
    return jwt.sign(payload as any, env.JWT_REFRESH_SECRET as any, { expiresIn: env.JWT_REFRESH_EXPIRES_IN as any });
}

export function verifyAccessToken(token: string): TokenPayload {
    return jwt.verify(token, env.JWT_ACCESS_SECRET as any) as unknown as TokenPayload;
}

export function verifyRefreshToken(token: string): TokenPayload {
    return jwt.verify(token, env.JWT_REFRESH_SECRET as any) as unknown as TokenPayload;
}
