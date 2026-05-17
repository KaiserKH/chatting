import { PrismaClient, RoleName } from '@prisma/client';
import bcrypt from 'bcryptjs';
import dotenv from 'dotenv';
import path from 'node:path';

const prisma = new PrismaClient();

dotenv.config({ path: path.resolve(process.cwd(), '..', '.env') });
dotenv.config();

async function main() {
  const roles = [
    { name: RoleName.SUPER_ADMIN, displayName: 'Super Admin', rank: 100 },
    { name: RoleName.ADMIN, displayName: 'Admin', rank: 90 },
    { name: RoleName.MODERATOR, displayName: 'Moderator', rank: 80 },
    { name: RoleName.VERIFIED_USER, displayName: 'Verified User', rank: 60 },
    { name: RoleName.PREMIUM_USER, displayName: 'Premium User', rank: 40 },
    { name: RoleName.NORMAL_USER, displayName: 'Normal User', rank: 10 }
  ];

  for (const role of roles) {
    await prisma.role.upsert({
      where: { name: role.name },
      update: {},
      create: role
    });
  }

  const permissionKeys = [
    'canVideoCall',
    'canVoiceCall',
    'canDeleteMessagesForEveryone',
    'canEditMessages',
    'canUploadHDMedia',
    'canUploadVideo',
    'canSendVoiceNotes',
    'canUseBfTag',
    'canUseGfTag',
    'canHideLastSeen',
    'canHideProfilePhoto',
    'canUseStories',
    'canUseAnimatedThemes',
    'canBypassFriendRequest',
    'canPinMessages',
    'canStarMessages',
    'canViewAdminLogs',
    'canAccessPremiumThemes'
  ];

  for (const key of permissionKeys) {
    await prisma.permission.upsert({
      where: { key },
      update: {},
      create: {
        key,
        description: key,
        defaultValue: key === 'canBypassFriendRequest' || key === 'canViewAdminLogs'
      }
    });
  }

  const adminRole = await prisma.role.findUnique({ where: { name: RoleName.SUPER_ADMIN } });
  if (!adminRole) {
    throw new Error('Super admin role was not created');
  }

  const username = process.env.SEED_SUPER_ADMIN_USERNAME ?? 'superadmin';
  const email = process.env.SEED_SUPER_ADMIN_EMAIL ?? 'admin@platform.local';
  const password = process.env.SEED_SUPER_ADMIN_PASSWORD ?? 'Admin@123456!';
  const phone = process.env.SEED_SUPER_ADMIN_PHONE ?? '+10000000000';

  const passwordHash = await bcrypt.hash(password, 12);

  await prisma.user.upsert({
    where: { username },
    update: {
      email,
      phone,
      passwordHash,
      roleId: adminRole.id,
      isEmailVerified: true,
      displayName: 'Super Admin'
    },
    create: {
      username,
      email,
      phone,
      passwordHash,
      roleId: adminRole.id,
      isEmailVerified: true,
      displayName: 'Super Admin'
    }
  });

  const tags = [
    { key: 'friend', displayName: 'Friend' },
    { key: 'close_friend', displayName: 'Close Friend' },
    { key: 'best_friend', displayName: 'Best Friend' },
    { key: 'bf', displayName: 'BF' },
    { key: 'gf', displayName: 'GF' },
    { key: 'partner', displayName: 'Partner' },
    { key: 'family', displayName: 'Family' }
  ];

  for (const tag of tags) {
    await prisma.relationshipTag.upsert({
      where: { key: tag.key },
      update: {},
      create: {
        key: tag.key,
        displayName: tag.displayName,
        permissions: {},
        theme: {},
        wallpapers: {}
      }
    });
  }
}

main()
  .then(async () => {
    await prisma.$disconnect();
  })
  .catch(async (error) => {
    console.error(error);
    await prisma.$disconnect();
    process.exit(1);
  });
