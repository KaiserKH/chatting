import jwt from 'jsonwebtoken'
import { env } from '../config/env.js'

export function authenticate(req, _res, next) {
  const token = req.cookies.accessToken

  if (!token) {
    req.user = null
    return next()
  }

  try {
    req.user = jwt.verify(token, env.jwtAccessSecret)
  } catch {
    req.user = null
  }

  return next()
}
