import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, Table, buttonStyle, inputStyle, colors } from './ui';

interface ConfigFile {
    name: string;
    size: number;
}

interface Backup {
    id: number;
    file: string;
    created_at: string;
}

const ConfigComponent: React.FC = () => {
    const server = useServerUuid();
    const [files, setFiles] = useState<ConfigFile[] | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const [selected, setSelected] = useState<string | null>(null);
    const [content, setContent] = useState('');
    const [loadingContent, setLoadingContent] = useState(false);
    const [dirty, setDirty] = useState(false);
    const [saving, setSaving] = useState(false);
    const [backups, setBackups] = useState<Backup[]>([]);
    const [loadingBackups, setLoadingBackups] = useState(false);
    const [restoring, setRestoring] = useState<number | null>(null);

    const loadFiles = () => {
        setError(null);
        mtApi<{ files: ConfigFile[] }>(mtUrl(server, '/config'))
            .then((d) => setFiles(d.files || []))
            .catch((e: Error) => setError(e.message));
    };

    useEffect(() => {
        if (server) loadFiles();
    }, [server]);

    const selectFile = async (name: string) => {
        setSelected(name);
        setDirty(false);
        setBackups([]);
        setLoadingContent(true);
        setNotice(null);
        try {
            const d = await mtApi<{ content: string }>(mtUrl(server, `/config/${encodeURIComponent(name)}/raw`));
            setContent(d.content || '');
        } catch (e) {
            setContent('');
            setNotice('Failed to read file: ' + (e as Error).message);
        } finally {
            setLoadingContent(false);
        }
        loadBackups(name);
    };

    const loadBackups = async (name: string) => {
        setLoadingBackups(true);
        try {
            const d = await mtApi<{ backups: Backup[] }>(mtUrl(server, `/config/${encodeURIComponent(name)}/backups`));
            setBackups(d.backups || []);
        } catch {
            setBackups([]);
        } finally {
            setLoadingBackups(false);
        }
    };

    const save = async () => {
        if (!selected) return;
        setSaving(true);
        setNotice(null);
        try {
            await mtApi(mtUrl(server, `/config/${encodeURIComponent(selected)}/raw`), {
                method: 'PUT',
                body: { content },
            });
            setNotice(`Saved ${selected} to the server (previous content backed up).`);
            setDirty(false);
            loadBackups(selected);
            loadFiles();
        } catch (e) {
            setNotice('Save failed: ' + (e as Error).message);
        } finally {
            setSaving(false);
        }
    };

    const restore = async (backup: Backup) => {
        if (!selected || !window.confirm(`Restore ${selected} from a backup taken ${backup.created_at}?`)) return;
        setRestoring(backup.id);
        setNotice(null);
        try {
            await mtApi(mtUrl(server, `/config/${encodeURIComponent(selected)}/restore/${backup.id}`), {
                method: 'POST',
            });
            setNotice(`Restored ${selected} from the backup.`);
            setDirty(false);
            setContent((await mtApi<{ content: string }>(mtUrl(server, `/config/${encodeURIComponent(selected)}/raw`))).content || '');
        } catch (e) {
            setNotice('Restore failed: ' + (e as Error).message);
        } finally {
            setRestoring(null);
        }
    };

    const editorTextarea: React.CSSProperties = {
        ...inputStyle,
        fontFamily: 'monospace',
        fontSize: 12,
        minHeight: 320,
        lineHeight: 1.5,
        whiteSpace: 'pre',
        resize: 'vertical',
    };

    return (
        <div>
            {notice && <Message tone={notice.includes('failed') ? 'error' : 'success'}>{notice}</Message>}
            {error && <Message tone="error">{error}</Message>}

            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Config</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Edit root-level text files on this server (server.properties, bukkit.yml, paper configs, ...). A
                    backup is kept automatically before every save.
                </div>
            </div>

            <Card
                title="Files"
                right={
                    <button style={buttonStyle()} onClick={loadFiles}>
                        Refresh
                    </button>
                }
            >
                {!files ? (
                    <SpinnerScreen label="Listing server root..." />
                ) : files.length === 0 ? (
                    <div style={{ color: colors.muted, fontSize: 13 }}>No editable files found in the server root.</div>
                ) : (
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                        {files.map((file) => {
                            const active = selected === file.name;
                            return (
                                <button
                                    key={file.name}
                                    onClick={() => selectFile(file.name)}
                                    style={{
                                        ...buttonStyle(active ? 'primary' : 'default'),
                                        margin: 0,
                                        fontFamily: 'monospace',
                                        fontSize: 12,
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: 6,
                                    }}
                                >
                                    {file.name}
                                    <span style={{ opacity: 0.6, fontWeight: 400 }}>{fmtBytes(file.size)}</span>
                                </button>
                            );
                        })}
                    </div>
                )}
            </Card>

            {selected && (
                <>
                    <div style={{ height: 12 }} />
                    <Card
                        title={`Editing: ${selected}`}
                        right={
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                {dirty && <Badge color={colors.warn}>unsaved</Badge>}
                                <button style={buttonStyle('primary')} onClick={save} disabled={saving}>
                                    {saving ? 'Saving...' : 'Save'}
                                </button>
                            </div>
                        }
                    >
                        {loadingContent ? (
                            <SpinnerScreen label="Reading file..." />
                        ) : (
                            <>
                                <textarea
                                    style={editorTextarea}
                                    value={content}
                                    onChange={(e) => {
                                        setContent(e.target.value);
                                        setDirty(true);
                                    }}
                                    spellCheck={false}
                                />
                                <div style={{ color: colors.muted, fontSize: 12, marginTop: 8 }}>
                                    Also restart the server for most server properties to take effect.
                                </div>
                            </>
                        )}
                    </Card>

                    <div style={{ height: 12 }} />
                    <Card title={`Backups for ${selected}`} right={<Badge>{backups.length}</Badge>}>
                        {loadingBackups ? (
                            <SpinnerScreen label="Loading backups..." />
                        ) : backups.length === 0 ? (
                            <div style={{ color: colors.muted, fontSize: 13 }}>No backups yet. They are created automatically on save.</div>
                        ) : (
                            <Table head={['Id', 'Created', '']}>
                                {backups.map((backup) => (
                                    <tr key={backup.id} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                        <td style={{ padding: '8px 10px', color: colors.muted, fontFamily: 'monospace' }}>
                                            #{backup.id}
                                        </td>
                                        <td style={{ padding: '8px 10px', color: colors.text }}>{backup.created_at}</td>
                                        <td style={{ padding: '8px 10px', textAlign: 'right' }}>
                                            <button
                                                style={buttonStyle('default')}
                                                onClick={() => restore(backup)}
                                                disabled={restoring === backup.id}
                                            >
                                                {restoring === backup.id ? 'Restoring...' : 'Restore'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </Table>
                        )}
                    </Card>
                </>
            )}
        </div>
    );
};

export default ConfigComponent;