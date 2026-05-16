import dotenv from 'dotenv'

dotenv.config({ path: process.env.ENV_FILE || '../../.env' })

export const env = {
  nodeEnv: process.env.NODE_ENV ?? 'development',
  port: Number(process.env.PORT ?? 4000),
  clientUrl: process.env.CLIENT_URL ?? 'http://localhost:5173',
  databaseUrl: process.env.DATABASE_URL ?? '',
  jwtAccessSecret: process.env.JWT_ACCESS_SECRET ?? 'replace-me',
  jwtRefreshSecret: process.env.JWT_REFRESH_SECRET ?? 'replace-me',
  cookieSecret: process.env.COOKIE_SECRET ?? 'replace-me',
}
