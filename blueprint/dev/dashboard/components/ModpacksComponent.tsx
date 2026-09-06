import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, Field, Table, buttonStyle, inputStyle, colors } from './ui';

interface ModEntry {
    name: string;
    size: number;
    enabled: boolean;
}

interface SearchHit {
    id: string;
    name: string;
    description: string;
    source: string;
}

const ModpacksComponent: React.FC = () => {
    const server = useServerUuid();
    const [mods, setMods] = useState<ModEntry[] | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const [query, setQuery] = useState('');
    const [searching, setSearching] = useState(false);
    const [results, setResults] = useState<SearchHit[]>([]);
    const [provider, setProvider] = useState('modrinth');
    const [installingId, setInstallingId] = useState<string | null>(null);

    const [manualUrl, setManualUrl] = useState('');
    const [manualName, setManualName] = useState('');

    const load = () => {
        setError(null);
        mtApi<{ mods: ModEntry[] }>(mtUrl(server, '/mods'))
            .then((d) => setMods(d.mods || []))
            .catch((e: Error) => setError(e.message));
    };

    useEffect(() => {
        if (server) load();
    }, [server]);

    const remove = async (mod: ModEntry) => {
        setNotice(null);
        if (!window.confirm(`Delete ${mod.name} from /mods?`)) return;
        try {
            await mtApi(mtUrl(server, `/mods/${encodeURIComponent(mod.name)}`), { method: 'DELETE' });
            load();
        } catch (e) {
            setNotice('Failed: ' + (e as Error).message);
        }
    };

    const search = async () => {
        if (!query.trim()) return;
        setSearching(true);
        setResults([]);
        try {
            const d = await mtApi<{ provider: string; results: SearchHit[] }>(mtUrl(server, '/mods/search'), {
                method: 'POST',
                body: { query },
            });
            setProvider(d.provider || 'modrinth');
            setResults(d.results || []);
        } catch (e) {
            setNotice('Search failed: ' + (e as Error).message);
        } finally {
            setSearching(false);
        }
    };

    const install = async (hit: SearchHit) => {
        setInstallingId(hit.id);
        setNotice(null);
        try {
            await mtApi(mtUrl(server, '/mods/install'), {
                method: 'POST',
                body: { source: hit.source || provider, id: hit.id },
            });
            setNotice(`Started download of ${hit.name}. It will appear in /mods once Wings finishes.`);
            setResults([]);
            setQuery('');
            setTimeout(load, 1500);
        } catch (e) {
            setNotice('Install failed: ' + (e as Error).message);
        } finally {
            setInstallingId(null);
        }
    };

    const installManual = async () => {
        if (!manualUrl.trim()) return;
        setNotice(null);
        try {
            await mtApi(mtUrl(server, '/mods'), {
                method: 'POST',
                body: { url: manualUrl.trim(), filename: manualName.trim() || undefined },
            });
            setNotice('Started a manual download into /mods.');
            setManualUrl('');
            setManualName('');
            setTimeout(load, 1500);
        } catch (e) {
            setNotice('Install failed: ' + (e as Error).message);
        }
    };

    return (
        <div>
            {notice && <Message tone={notice.startsWith('Failed') ? 'error' : 'success'}>{notice}</Message>}
            {error && <Message tone="error">{error}</Message>}

            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Mods</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Manages the .jar files in /mods (Fabric, Forge, NeoForge, Quilt). Downloads are handled by Wings.
                </div>
            </div>

            <Card title="Search &amp; install" right={<Badge color={colors.accent}>{provider}</Badge>}>
                <div style={{ display: 'flex', gap: 8 }}>
                    <input
                        style={inputStyle}
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && search()}
                        placeholder="Search for a mod..."
                        spellCheck={false}
                    />
                    <button style={buttonStyle('primary')} onClick={search} disabled={searching}>
                        {searching ? 'Searching...' : 'Search'}
                    </button>
                </div>

                {results.length > 0 && (
                    <div style={{ marginTop: 12 }}>
                        <Table head={['Name', 'Description', '']}>
                            {results.map((hit) => (
                                <tr key={hit.id} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                    <td style={{ padding: '8px 10px', color: colors.text, fontWeight: 600 }}>{hit.name}</td>
                                    <td style={{ padding: '8px 10px', color: colors.muted, fontSize: 12 }}>{hit.description}</td>
                                    <td style={{ padding: '8px 10px', textAlign: 'right' }}>
                                        <button style={buttonStyle('success')} onClick={() => install(hit)} disabled={installingId === hit.id}>
                                            {installingId === hit.id ? 'Installing...' : 'Install'}
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </Table>
                    </div>
                )}
                {searching && <SpinnerScreen label="Searching provider..." />}

                <div style={{ borderTop: `1px solid ${colors.border}`, marginTop: 14, paddingTop: 14 }}>
                    <Field label="Manual install (direct URL)">
                        <div style={{ display: 'flex', gap: 8 }}>
                            <input
                                style={inputStyle}
                                value={manualUrl}
                                onChange={(e) => setManualUrl(e.target.value)}
                                placeholder="https://example.com/mod.jar"
                                spellCheck={false}
                            />
                            <input
                                style={{ ...inputStyle, width: 200 }}
                                value={manualName}
                                onChange={(e) => setManualName(e.target.value)}
                                placeholder="file name (.jar)"
                                spellCheck={false}
                            />
                            <button style={buttonStyle()} onClick={installManual}>
                                Download
                            </button>
                        </div>
                    </Field>
                </div>
            </Card>

            <div style={{ height: 12 }} />

            <Card title="Installed mods" right={<Badge>{mods ? mods.length : '—'}</Badge>}>
                {!mods ? (
                    <SpinnerScreen label="Listing /mods..." />
                ) : mods.length === 0 ? (
                    <div style={{ color: colors.muted, fontSize: 13 }}>No mods found in /mods yet.</div>
                ) : (
                    <Table head={['File', 'Size', '']}>
                        {mods.map((mod) => (
                            <tr key={mod.name} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                <td style={{ padding: '8px 10px', color: colors.text, fontFamily: 'monospace', fontSize: 12 }}>
                                    {mod.name}
                                </td>
                                <td style={{ padding: '8px 10px', color: colors.muted }}>{fmtBytes(mod.size)}</td>
                                <td style={{ padding: '8px 10px', textAlign: 'right' }}>
                                    <button style={buttonStyle('danger')} onClick={() => remove(mod)}>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </Table>
                )}
            </Card>
        </div>
    );
};

export default ModpacksComponent;