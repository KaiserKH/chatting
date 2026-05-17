import { z } from 'zod';

export const registerSchema = z.object({
  username: z.string().min(3).max(24),
  phone: z.string().min(7).max(24),
  email: z.string().email().optional().or(z.literal('')),
  password: z.string().min(8).max(128),
  deviceName: z.string().min(2).max(64).default('Unknown device')
});

export const loginSchema = z.object({
  identifier: z.string().min(3),
  password: z.string().min(8),
  deviceName: z.string().min(2).max(64).default('Unknown device')
});
