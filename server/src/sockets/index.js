import { Server } from 'socket.io'
import jwt from 'jsonwebtoken'
import { env } from '../config/env.js'

function canEmitTypingEvent(socket, toUserId) {
  if (!socket.user?.sub) return false
  if (!toUserId || typeof toUserId !== 'string') return false
  if (toUserId === socket.user.sub) return false

  const allowedTypingTargets = socket.data.allowedTypingTargets

  if (!allowedTypingTargets) return false

  return allowedTypingTargets.has(toUserId)
}

export function setupSocketServer(httpServer) {
  const io = new Server(httpServer, {
    cors: {
      origin: env.clientUrl,
      credentials: true,
    },
  })

  io.use((socket, next) => {
    const token = socket.handshake.auth?.token

    if (!token) return next(new Error('Unauthorized socket'))

    try {
      socket.user = jwt.verify(token, env.jwtAccessSecret)
      socket.data.allowedTypingTargets = new Set()
      return next()
    } catch {
      return next(new Error('Unauthorized socket'))
    }
  })

  io.on('connection', (socket) => {
    socket.join(`user:${socket.user.sub}`)

    socket.on('typing:start', ({ toUserId }) => {
      if (!canEmitTypingEvent(socket, toUserId)) return

      io.to(`user:${toUserId}`).emit('typing:start', { fromUserId: socket.user.sub })
    })

    socket.on('typing:stop', ({ toUserId }) => {
      if (!canEmitTypingEvent(socket, toUserId)) return

      io.to(`user:${toUserId}`).emit('typing:stop', { fromUserId: socket.user.sub })
    })
  })

  return io
}
