import { NextFunction, Request, Response } from 'express';

export function notFoundHandler(_request: Request, response: Response) {
  return response.status(404).json({ success: false, message: 'Route not found' });
}

export function errorHandler(error: Error, _request: Request, response: Response, _next: NextFunction) {
  console.error(error);
  return response.status(500).json({ success: false, message: error.message || 'Internal server error' });
}
