import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, Field, Table, buttonStyle, inputStyle, colors } from './ui';

interface PluginEntry {
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

const PluginsComponent: React.FC = () => {
    const server = useServerUuid();
    const [plugins, setPlugins] = useState<PluginEntry[] | null>(null);
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
        mtApi<{ plugins: PluginEntry[] }>(mtUrl(server, '/plugins'))
            .then((d) => setPlugins(d.plugins || []))
            .catch((e: Error) => setError(e.message));
    };

    useEffect(() => {
        if (server) load();
    }, [server]);

    const toggle = async (plugin: PluginEntry) => {
        setNotice(null);
        try {
            await mtApi(mtUrl(server, `/plugins/${encodeURIComponent(plugin.name)}/enabled`), {
                method: 'PUT',
                body: { enabled: !plugin.enabled },
            });
            load();
        } catch (e) {
            setNotice('Failed: ' + (e as Error).message);
        }
    };

    const remove = async (plugin: PluginEntry) => {
        setNotice(null);
        if (!window.confirm(`Delete ${plugin.name} from /plugins?`)) return;
        try {
            await mtApi(mtUrl(server, `/plugins/${encodeURIComponent(plugin.name)}`), { method: 'DELETE' });
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
            const d = await mtApi<{ provider: string; results: SearchHit[] }>(mtUrl(server, '/plugins/search'), {
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
            await mtApi(mtUrl(server, '/plugins/install'), {
                method: 'POST',
                body: { source: hit.source || provider, id: hit.id },
            });
            setNotice(`Started download of ${hit.name}. It will appear in /plugins once Wings finishes.`);
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
            await mtApi(mtUrl(server, '/plugins'), {
                method: 'POST',
                body: { url: manualUrl.trim(), filename: manualName.trim() || undefined },
            });
            setNotice('Started a manual download into /plugins.');
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
                <h2 style={{ color: colors.text, margin: 0 }}>Plugins</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Manages the .jar files in /plugins on this server. Disabled plugins are renamed to .jar.disabled.
                </div>
            </div>

            <Card title="Search &amp; install" right={<Badge color={colors.accent}>{provider}</Badge>}>
                <div style={{ display: 'flex', gap: 8 }}>
                    <input
                        style={inputStyle}
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && search()}
                        placeholder="Search for a plugin..."
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
                    <Field label="Manual install (server-side/Wings download)" hint="Wings downloads the file directly — the server does not need to be running.">
                        <div style={{ display: 'flex', gap: 8 }}>
                            <input
                                style={inputStyle}
                                value={manualUrl}
                                onChange={(e) => setManualUrl(e.target.value)}
                                placeholder="https://example.com/plugin.jar"
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

            <Card title="Installed plugins" right={<Badge>{plugins ? plugins.length : '—'}</Badge>}>
                {!plugins ? (
                    <SpinnerScreen label="Listing /plugins..." />
                ) : plugins.length === 0 ? (
                    <div style={{ color: colors.muted, fontSize: 13 }}>No plugins found in /plugins yet.</div>
                ) : (
                    <Table head={['File', 'Size', 'Status', '']}>
                        {plugins.map((plugin) => (
                            <tr key={plugin.name} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                <td style={{ padding: '8px 10px', color: colors.text, fontFamily: 'monospace', fontSize: 12 }}>
                                    {plugin.name}
                                </td>
                                <td style={{ padding: '8px 10px', color: colors.muted }}>{fmtBytes(plugin.size)}</td>
                                <td style={{ padding: '8px 10px' }}>
                                    <Badge color={plugin.enabled ? colors.success : colors.muted}>
                                        {plugin.enabled ? 'Enabled' : 'Disabled'}
                                    </Badge>
                                </td>
                                <td style={{ padding: '8px 10px', textAlign: 'right' }}>
                                    <button style={buttonStyle(plugin.enabled ? 'default' : 'success')} onClick={() => toggle(plugin)}>
                                        {plugin.enabled ? 'Disable' : 'Enable'}
                                    </button>
                                    <button style={buttonStyle('danger')} onClick={() => remove(plugin)}>
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

export default PluginsComponent;