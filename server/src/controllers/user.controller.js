export const userController = {
  me: (req, res) => res.json({ success: true, data: req.user ?? null }),
}
