import dotenv from 'dotenv'
import path from 'node:path'

const rootEnvPath = path.resolve(process.cwd(), '../.env')
dotenv.config()
dotenv.config({ path: rootEnvPath, override: false })

const envConfig = {
  nodeEnv: process.env.NODE_ENV ?? 'development',
  port: Number(process.env.PORT ?? 4000),
  clientUrl: process.env.CLIENT_URL ?? 'http://localhost:5173',
  databaseUrl: process.env.DATABASE_URL ?? '',
  jwtAccessSecret: process.env.JWT_ACCESS_SECRET ?? 'replace-me',
  jwtRefreshSecret: process.env.JWT_REFRESH_SECRET ?? 'replace-me',
  cookieSecret: process.env.COOKIE_SECRET ?? 'replace-me',
}

if (envConfig.nodeEnv === 'production') {
  const insecureSecrets = [
    envConfig.jwtAccessSecret,
    envConfig.jwtRefreshSecret,
    envConfig.cookieSecret,
  ].some((value) => !value || value === 'replace-me')

  if (insecureSecrets) {
    throw new Error('Missing secure JWT/COOKIE secrets for production environment')
  }
}

export const env = envConfig
