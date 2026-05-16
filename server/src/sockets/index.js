import { Server } from 'socket.io'
import jwt from 'jsonwebtoken'
import { env } from '../config/env.js'

export function setupSocketServer(httpServer) {
  const io = new Server(httpServer, {
    cors: {
      origin: env.clientUrl,
      credentials: true,
    },
  })

  io.use((socket, next) => {
    const token = socket.handshake.auth?.token

    if (!token) return next()

    try {
      socket.user = jwt.verify(token, env.jwtAccessSecret)
      return next()
    } catch {
      return next(new Error('Unauthorized socket'))
    }
  })

  io.on('connection', (socket) => {
    if (socket.user?.sub) socket.join(`user:${socket.user.sub}`)

    socket.on('typing:start', ({ toUserId }) => {
      io.to(`user:${toUserId}`).emit('typing:start', { fromUserId: socket.user?.sub })
    })

    socket.on('typing:stop', ({ toUserId }) => {
      io.to(`user:${toUserId}`).emit('typing:stop', { fromUserId: socket.user?.sub })
    })
  })

  return io
}
