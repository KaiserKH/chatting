#!/usr/bin/env node
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');

const root = path.resolve(__dirname, '..');
const envPath = path.join(root, '.env');
const examplePath = path.join(root, '.env.example');

function secret(bytes = 32) {
  return crypto.randomBytes(bytes).toString('hex');
}

const defaults = {
  JWT_ACCESS_SECRET: `msgplatform_${secret(24)}`,
  JWT_REFRESH_SECRET: `msgplatform_${secret(24)}`,
  JWT_ACCESS_EXPIRES_IN: '15m',
  JWT_REFRESH_EXPIRES_IN: '7d',
  BCRYPT_SALT_ROUNDS: '12',
  COOKIE_SECRET: `msgplatform_${secret(24)}`,
  COOKIE_SECURE: 'false',
  COOKIE_SAME_SITE: 'strict',
  COOKIE_HTTP_ONLY: 'true',
  NODE_ENV: 'development',
  PORT: '5000',
  CLIENT_URL: 'http://localhost:5173',
  API_PREFIX: '/api/v1',
  DATABASE_URL: 'mysql://root:password@localhost:3306/messaging_platform',
  CLOUDINARY_CLOUD_NAME: 'your_cloud_name',
  CLOUDINARY_API_KEY: 'your_cloud_name',
  CLOUDINARY_API_SECRET: secret(24),
  RATE_LIMIT_WINDOW_MS: '900000',
  RATE_LIMIT_MAX_REQUESTS: '100',
  SEED_SUPER_ADMIN_USERNAME: 'superadmin',
  SEED_SUPER_ADMIN_EMAIL: 'admin@platform.local',
  SEED_SUPER_ADMIN_PASSWORD: `Admin@${secret(4)}!`,
  SEED_SUPER_ADMIN_PHONE: '+10000000000',
  TURN_SERVER_URL: 'turn:openrelay.metered.ca:80',
  TURN_SERVER_USERNAME: 'openrelayproject',
  TURN_SERVER_CREDENTIAL: 'openrelayproject'
};

const envLines = Object.entries(defaults).map(([key, value]) => `${key}=${value}`);
fs.writeFileSync(envPath, `${envLines.join('\n')}\n`);

if (!fs.existsSync(examplePath)) {
  fs.writeFileSync(examplePath, `${envLines.join('\n')}\n`);
}

console.log(`Wrote secrets to ${envPath}`);
