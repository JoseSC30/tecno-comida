export function asset(path: string): string {
    const base = (window as any).__APP_URL__ || '';
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return `${base}/${cleanPath}`;
}
