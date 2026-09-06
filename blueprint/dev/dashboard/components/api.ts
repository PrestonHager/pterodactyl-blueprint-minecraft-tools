import { ServerContext } from '@/state/server';

export interface MtApiOptions {
    method?: string;
    body?: unknown;
}

export async function mtApi<T = unknown>(path: string, options: MtApiOptions = {}): Promise<T> {
    const resp = await fetch(path, {
        method: options.method || 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
    });
    const data = (await resp.json().catch(() => ({}))) as Record<string, unknown>;
    if (!resp.ok) {
        const message =
            (typeof data.message === 'string' && data.message) ||
            (typeof data.error === 'string' && data.error) ||
            `Request failed (${resp.status})`;
        throw new Error(message);
    }
    return data as T;
}

/** Returns the UUID of the currently open server (or undefined when outside a server page). */
export function useServerUuid(): string | undefined {
    return ServerContext.useStoreState((state) => state.server.data?.uuid);
}

/** Builds the Minecraft Tools client API url for the open server. */
export function mtUrl(server: string | undefined, path: string): string {
    return `/api/client/extensions/minecrafttools/servers/${server}${path}`;
}

export function fmtBytes(n: number): string {
    if (n >= 1048576) {
        return `${(n / 1048576).toFixed(1)} MB`;
    }
    if (n >= 1024) {
        return `${(n / 1024).toFixed(1)} KB`;
    }
    return `${n} B`;
}