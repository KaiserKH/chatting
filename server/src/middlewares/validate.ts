import { NextFunction, Request, Response } from 'express';
import { ZodSchema } from 'zod';
import { sanitizeInput } from '../utils/sanitize.js';

export function validateBody(schema: ZodSchema) {
  return (request: Request, response: Response, next: NextFunction) => {
    const sanitized = sanitizeInput(request.body);
    const result = schema.safeParse(sanitized);
    if (!result.success) {
      return response.status(400).json({ success: false, message: 'Validation failed', details: result.error.flatten() });
    }

    request.body = result.data;
    return next();
  };
}
