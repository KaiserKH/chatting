export const adminController = {
  dashboard: (_req, res) => res.json({ success: true, data: { users: 0, messages: 0 } }),
}
