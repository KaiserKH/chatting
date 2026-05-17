import { Response } from 'express';

export function ok(response: Response, data: unknown, message = 'OK', status = 200) {
  return response.status(status).json({ success: true, message, data });
}

export function fail(response: Response, message: string, status = 400, details?: unknown) {
  return response.status(status).json({ success: false, message, details });
}
