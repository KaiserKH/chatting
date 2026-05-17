import { CookieOptions, Response } from 'express';
import { env } from '../config/env.js';
import { cookieNames } from '../config/constants.js';

function cookieOptions(maxAgeMs: number): CookieOptions {
  return {
    httpOnly: env.COOKIE_HTTP_ONLY,
    secure: env.COOKIE_SECURE,
    sameSite: env.COOKIE_SAME_SITE,
    signed: true,
    maxAge: maxAgeMs,
    path: '/'
  };
}

export function setAuthCookies(response: Response, accessToken: string, refreshToken: string) {
  const refreshMaxAge = 1000 * 60 * 60 * 24 * 7;
  const accessMaxAge = 1000 * 60 * 15;
  response.cookie(cookieNames.accessToken, accessToken, cookieOptions(accessMaxAge));
  response.cookie(cookieNames.refreshToken, refreshToken, cookieOptions(refreshMaxAge));
}

export function clearAuthCookies(response: Response) {
  response.clearCookie(cookieNames.accessToken, { path: '/' });
  response.clearCookie(cookieNames.refreshToken, { path: '/' });
}
