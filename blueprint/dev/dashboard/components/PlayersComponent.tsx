import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, Table, buttonStyle, inputStyle, colors } from './ui';

interface PlayerEntry {
    name?: string;
    uuid?: string;
}

interface PlayersPayload {
    whitelist?: PlayerEntry[];
    ops?: PlayerEntry[];
    banned_players?: PlayerEntry[];
    banned_ips?: PlayerEntry[];
    whitelist_enabled?: boolean;
}

type FileKind = 'whitelist' | 'ops' | 'banned_players' | 'banned_ips';

const KIND_LABELS: Record<FileKind, string> = {
    whitelist: 'Whitelist (white-list.json)',
    ops: 'Operators (ops.json)',
    banned_players: 'Banned players (banned-players.json)',
    banned_ips: 'Banned IPs (banned-ips.json)',
};

const PlayersComponent: React.FC = () => {
    const server = useServerUuid();
    const [data, setData] = useState<PlayersPayload | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);
    const [consoleResult, setConsoleResult] = useState<string | null>(null);
    const [cmd, setCmd] = useState('whitelist list');
    const [newPlayer, setNewPlayer] = useState('');

    const load = () => {
        setError(null);
        setData(null);
        mtApi<PlayersPayload>(mtUrl(server, '/players'))
            .then(setData)
            .catch((e: Error) => setError(e.message));
    };

    useEffect(() => {
        if (server) load();
    }, [server]);

    if (error) {
        return <Message tone="error">{error}</Message>;
    }
    if (!data) {
        return <SpinnerScreen label="Reading server player files..." />;
    }

    const lists: Record<FileKind, PlayerEntry[]> = {
        whitelist: data.whitelist || [],
        ops: data.ops || [],
        banned_players: data.banned_players || [],
        banned_ips: data.banned_ips || [],
    };

    const addEntry = (kind: FileKind, entry: PlayerEntry) => {
        const next = [...lists[kind]];
        next.push(entry);
        lists[kind] = next;
        setData({ ...data, [kind]: next });
    };

    const removeEntry = (kind: FileKind, index: number) => {
        const next = lists[kind].filter((_, i) => i !== index);
        lists[kind] = next;
        setData({ ...data, [kind]: next });
    };

    const saveAll = async () => {
        setSaving(true);
        setSaved(null);
        try {
            await mtApi(mtUrl(server, '/players'), {
                method: 'PUT',
                body: {
                    whitelist: lists.whitelist,
                    ops: lists.ops,
                    banned_players: lists.banned_players,
                    banned_ips: lists.banned_ips,
                },
            });
            setSaved('Saved the current lists to the server.');
            load();
        } catch (e) {
            setSaved('Save failed: ' + (e as Error).message);
        } finally {
            setSaving(false);
        }
    };

    const addNew = (kind: FileKind) => {
        const name = newPlayer.trim();
        if (!name) return;
        const entry: PlayerEntry = kind === 'banned_ips' ? { name, uuid: undefined } : { name, uuid: undefined };
        addEntry(kind, entry);
        setNewPlayer('');
    };

    const sendCommand = async () => {
        setConsoleResult(null);
        try {
            await mtApi(mtUrl(server, '/players/command'), { method: 'POST', body: { command: cmd } });
            setConsoleResult('Sent: ' + cmd);
        } catch (e) {
            setConsoleResult('Failed: ' + (e as Error).message);
        }
    };

    return (
        <div>
            {saved && <Message tone={saved.startsWith('Save failed') ? 'error' : 'success'}>{saved}</Message>}

            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Players</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Edits the server&apos;s white-list.json / ops.json and ban files. Whitelist is{' '}
                    {data.whitelist_enabled ? 'enabled' : 'disabled'} in server.properties.
                </div>
            </div>

            <Card title="Console command" right={<Badge color={colors.warn}>quick action</Badge>}>
                <div style={{ display: 'flex', gap: 8 }}>
                    <input
                        style={inputStyle}
                        value={cmd}
                        onChange={(e) => setCmd(e.target.value)}
                        spellCheck={false}
                        placeholder="e.g. whitelist list, op Steve"
                    />
                    <button style={buttonStyle('primary')} onClick={sendCommand}>
                        Send
                    </button>
                </div>
                {consoleResult && <div style={{ color: colors.muted, fontSize: 12, marginTop: 8 }}>{consoleResult}</div>}
                <div style={{ color: colors.muted, fontSize: 12, marginTop: 8 }}>
                    Allowed commands: whitelist, op, deop, ban, pardon, kick, list, tps, players.
                </div>
            </Card>

            <div style={{ height: 12 }} />

            {(Object.keys(KIND_LABELS) as FileKind[]).map((kind) => (
                <div key={kind} style={{ marginBottom: 12 }}>
                    <Card
                        title={KIND_LABELS[kind]}
                        right={
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <input
                                    style={{ ...inputStyle, width: 180 }}
                                    value={newPlayer}
                                    onChange={(e) => setNewPlayer(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && addNew(kind)}
                                    placeholder="Name to add..."
                                />
                                <button style={buttonStyle('success')} onClick={() => addNew(kind)}>
                                    Add
                                </button>
                            </div>
                        }
                    >
                        {lists[kind].length === 0 ? (
                            <div style={{ color: colors.muted, fontSize: 13 }}>Empty — nothing listed yet.</div>
                        ) : (
                            <Table head={['Name', 'UUID', '']}>
                                {lists[kind].map((entry, index) => (
                                    <tr key={`${kind}-${index}`} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                        <td style={{ padding: '8px 10px', color: colors.text, fontWeight: 600 }}>
                                            {entry.name || entry.uuid}
                                        </td>
                                        <td style={{ padding: '8px 10px', color: colors.muted, fontFamily: 'monospace', fontSize: 12 }}>
                                            {entry.uuid || '-'}
                                        </td>
                                        <td style={{ padding: '8px 10px', textAlign: 'right' }}>
                                            <button style={buttonStyle('danger')} onClick={() => removeEntry(kind, index)}>
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </Table>
                        )}
                    </Card>
                </div>
            ))}

            <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                <button style={buttonStyle('primary')} onClick={saveAll} disabled={saving}>
                    {saving ? 'Saving...' : 'Save all files'}
                </button>
            </div>
        </div>
    );
};

export default PlayersComponent;