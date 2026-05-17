export const roleHierarchy = [
  'SUPER_ADMIN',
  'ADMIN',
  'MODERATOR',
  'VERIFIED_USER',
  'PREMIUM_USER',
  'NORMAL_USER'
] as const;

export type RoleName = (typeof roleHierarchy)[number];

export const cookieNames = {
  accessToken: 'mp_access_token',
  refreshToken: 'mp_refresh_token'
} as const;

export const permissionKeys = [
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
] as const;
