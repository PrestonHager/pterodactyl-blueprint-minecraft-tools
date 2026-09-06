import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, colors, Table } from './ui';

interface OverviewPayload {
    server?: { uuid?: string; name?: string; egg?: string };
    folders?: Record<string, boolean>;
    files?: string[];
    jars?: { name: string; size: number }[];
}

const MinecraftToolsSection: React.FC = () => {
    const server = useServerUuid();
    const [data, setData] = useState<OverviewPayload | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!server) return;
        mtApi<OverviewPayload>(mtUrl(server, '/'))
            .then(setData)
            .catch((e: Error) => setError(e.message));
    }, [server]);

    if (error) {
        return <Message tone="error">{error}</Message>;
    }
    if (!data) {
        return <SpinnerScreen label="Loading server overview..." />;
    }

    const folders = data.folders || {};
    const jars = data.jars || [];

    return (
        <div>
            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Minecraft Tools</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    {data.server?.name || 'This server'} {data.server?.egg ? `— ${data.server.egg} egg` : ''}
                </div>
            </div>

            <Card title="Server folders" right={<Badge color={colors.accent}>{Object.keys(folders).length} checked</Badge>}>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                    {Object.entries(folders).map(([folder, exists]) => (
                        <span
                            key={folder}
                            style={{
                                border: `1px solid ${colors.border}`,
                                borderRadius: 6,
                                padding: '6px 12px',
                                fontSize: 13,
                                color: exists ? colors.text : colors.muted,
                                backgroundColor: exists ? colors.panel : 'transparent',
                            }}
                        >
                            /{folder} {exists ? '✓' : '—'}
                        </span>
                    ))}
                </div>
            </Card>

            <div style={{ height: 12 }} />

            <Card title="Server jars" right={<Badge>{jars.length}</Badge>}>
                {jars.length === 0 ? (
                    <div style={{ color: colors.muted, fontSize: 13 }}>No .jar files found in the server root.</div>
                ) : (
                    <Table head={['File', 'Size']}>
                        {jars.map((jar) => (
                            <tr key={jar.name} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                <td style={{ padding: '8px 10px', color: colors.text }}>{jar.name}</td>
                                <td style={{ padding: '8px 10px', color: colors.muted }}>{fmtBytes(jar.size)}</td>
                            </tr>
                        ))}
                    </Table>
                )}
            </Card>

            <div style={{ height: 12 }} />

            <Card title="About this page">
                <div style={{ color: colors.muted, fontSize: 13, lineHeight: 1.6 }}>
                    Every Minecraft Tools page here works directly against the files on this server&apos;s Wings node.
                    Install plugins and mods, manage the whitelist, change the server version, generate the server icon
                    and edit configuration files — changes apply to this server immediately.
                </div>
            </Card>
        </div>
    );
};

export default MinecraftToolsSection;