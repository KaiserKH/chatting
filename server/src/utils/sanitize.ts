function sanitizeString(value: string): string {
  return value.replace(/<script.*?>.*?<\/script>/gis, '').replace(/[<>]/g, '');
}

export function sanitizeInput<T>(value: T): T {
  if (Array.isArray(value)) {
    return value.map((item) => sanitizeInput(item)) as T;
  }

  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.entries(value as Record<string, unknown>).map(([key, entry]) => [key, sanitizeInput(entry)])
    ) as T;
  }

  if (typeof value === 'string') {
    return sanitizeString(value) as T;
  }

  return value;
}
