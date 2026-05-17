import type { RoleName } from '../config/constants.js';
import 'express';

declare module 'express-serve-static-core' {
  interface Request {
    user?: {
      id: string;
      role: RoleName;
      username: string;
      sessionId: string;
    };
    session?: {
      id: string;
      userId: string;
    };
    auditContext?: {
      actionType: string;
      description: string;
      targetUserId?: string;
      affectedField?: string;
      metadata?: Record<string, unknown>;
    };
  }
}
