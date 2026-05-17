import dotenv from 'dotenv';
import path from 'node:path';
import { z } from 'zod';

dotenv.config({ path: path.resolve(process.cwd(), '..', '.env') });
dotenv.config();

const envSchema = z.object({
  JWT_ACCESS_SECRET: z.string().min(16),
  JWT_REFRESH_SECRET: z.string().min(16),
  JWT_ACCESS_EXPIRES_IN: z.string().default('15m'),
  JWT_REFRESH_EXPIRES_IN: z.string().default('7d'),
  BCRYPT_SALT_ROUNDS: z.coerce.number().int().min(10).default(12),
  COOKIE_SECRET: z.string().min(16),
  COOKIE_SECURE: z.coerce.boolean().default(false),
  COOKIE_SAME_SITE: z.enum(['strict', 'lax', 'none']).default('strict'),
  COOKIE_HTTP_ONLY: z.coerce.boolean().default(true),
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  PORT: z.coerce.number().int().default(5000),
  CLIENT_URL: z.string().url(),
  API_PREFIX: z.string().default('/api/v1'),
  DATABASE_URL: z.string().min(1),
  RATE_LIMIT_WINDOW_MS: z.coerce.number().int().positive().default(900000),
  RATE_LIMIT_MAX_REQUESTS: z.coerce.number().int().positive().default(100),
  SEED_SUPER_ADMIN_USERNAME: z.string().default('superadmin'),
  SEED_SUPER_ADMIN_EMAIL: z.string().email().default('admin@platform.local'),
  SEED_SUPER_ADMIN_PASSWORD: z.string().default('Admin@123456!'),
  SEED_SUPER_ADMIN_PHONE: z.string().default('+10000000000'),
  TURN_SERVER_URL: z.string().default('turn:openrelay.metered.ca:80'),
  TURN_SERVER_USERNAME: z.string().default('openrelayproject'),
  TURN_SERVER_CREDENTIAL: z.string().default('openrelayproject'),
  CLOUDINARY_CLOUD_NAME: z.string().optional(),
  CLOUDINARY_API_KEY: z.string().optional(),
  CLOUDINARY_API_SECRET: z.string().optional()
});

export const env = envSchema.parse(process.env);
