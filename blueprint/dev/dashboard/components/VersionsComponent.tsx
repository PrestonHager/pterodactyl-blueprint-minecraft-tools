import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid, fmtBytes } from './api';
import { Card, Badge, SpinnerScreen, Message, Field, Table, buttonStyle, inputStyle, colors } from './ui';

interface JarEntry {
    name: string;
    size: number;
}

interface BuildEntry {
    build: string;
    channel?: string | null;
    installer?: string | null;
}

const TYPES = ['paper', 'purpur', 'fabric', 'vanilla', 'spigot', 'custom'];

const VersionsComponent: React.FC = () => {
    const server = useServerUuid();
    const [jars, setJars] = useState<JarEntry[] | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const [type, setType] = useState('paper');
    const [versions, setVersions] = useState<string[]>([]);
    const [builds, setBuilds] = useState<BuildEntry[]>([]);
    const [mcVersion, setMcVersion] = useState('');
    const [build, setBuild] = useState('');
    const [installer, setInstaller] = useState<string | null>(null);
    const [jarName, setJarName] = useState('server.jar');
    const [loadingList, setLoadingList] = useState(false);
    const [loadingBuilds, setLoadingBuilds] = useState(false);
    const [installing, setInstalling] = useState(false);
    const [customUrl, setCustomUrl] = useState('');

    const loadJars = () => {
        setError(null);
        mtApi<{ jars: JarEntry[]; target?: string }>(mtUrl(server, '/versions'))
            .then((d) => {
                setJars(d.jars || []);
                if (d.target) setJarName(d.target);
            })
            .catch((e: Error) => setError(e.message));
    };

    useEffect(() => {
        if (server) loadJars();
    }, [server]);

    const loadVersions = async (nextType: string) => {
        if (nextType === 'custom') {
            setVersions([]);
            setBuilds([]);
            setMcVersion('');
            return;
        }
        setLoadingList(true);
        setVersions([]);
        setBuilds([]);
        setMcVersion('');
        setBuild('');
        try {
            const d = await mtApi<{ versions: string[] }>(mtUrl(server, '/versions/list'), {
                method: 'POST',
                body: { type: nextType },
            });
            setVersions(d.versions || []);
        } catch (e) {
            setNotice('Failed to load versions: ' + (e as Error).message);
        } finally {
            setLoadingList(false);
        }
    };

    const loadBuilds = async (nextType: string, nextVersion: string) => {
        if (nextType === 'custom' || !nextVersion || nextType === 'spigot' || nextType === 'vanilla') {
            setBuilds([]);
            setBuild('');
            return;
        }
        setLoadingBuilds(true);
        setBuilds([]);
        setBuild('');
        try {
            const d = await mtApi<{ builds: BuildEntry[] }>(mtUrl(server, '/versions/builds'), {
                method: 'POST',
                body: { type: nextType, version: nextVersion },
            });
            setBuilds(d.builds || []);
        } catch (e) {
            setNotice('Failed to load builds: ' + (e as Error).message);
        } finally {
            setLoadingBuilds(false);
        }
    };

    const changeType = (nextType: string) => {
        setType(nextType);
        setMcVersion('');
        setBuild('');
        setInstaller(null);
        loadVersions(nextType);
    };

    const changeVersion = (nextVersion: string) => {
        setMcVersion(nextVersion);
        setBuild('');
        setInstaller(null);
        loadBuilds(type, nextVersion);
    };

    const changeBuild = (nextBuild: string) => {
        const found = builds.find((b) => b.build === nextBuild);
        setInstaller(found && found.installer ? found.installer : null);
        setBuild(nextBuild);
    };

    const install = async () => {
        setNotice(null);
        setInstalling(true);
        try {
            const body: Record<string, unknown> = { type, target: jarName };
            if (type === 'custom') {
                if (!customUrl.trim()) throw new Error('Provide a download URL for the custom server jar.');
                body.url = customUrl.trim();
            } else {
                if (!mcVersion) throw new Error('Select a Minecraft version first.');
                body.version = mcVersion;
                if (build) body.build = build;
                if (installer) body.installer = installer;
            }
            await mtApi(mtUrl(server, '/versions/install'), { method: 'POST', body });
            setNotice(`Started download as ${jarName}. The previous jar (if any) was moved to ${jarName}.previous.`);
            setTimeout(loadJars, 2000);
        } catch (e) {
            setNotice('Install failed: ' + (e as Error).message);
        } finally {
            setInstalling(false);
        }
    };

    return (
        <div>
            {notice && <Message tone={notice.startsWith('Install failed') ? 'error' : 'success'}>{notice}</Message>}
            {error && <Message tone="error">{error}</Message>}

            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Versions</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Installs a server jar into the server root as <code>{jarName}</code>. The previous jar is kept as{' '}
                    <code>{jarName}.previous</code>.
                </div>
            </div>

            <Card title="Install a server version">
                <Field label="Source">
                    <select
                        style={inputStyle}
                        value={type}
                        onChange={(e) => changeType(e.target.value)}
                        disabled={installing}
                    >
                        {TYPES.map((t) => (
                            <option key={t} value={t}>
                                {t[0].toUpperCase() + t.slice(1)}
                            </option>
                        ))}
                    </select>
                </Field>

                {type === 'custom' ? (
                    <Field label="Direct .jar URL" hint="Wings will download this file into the server root.">
                        <input
                            style={inputStyle}
                            value={customUrl}
                            onChange={(e) => setCustomUrl(e.target.value)}
                            placeholder="https://example.com/server.jar"
                            spellCheck={false}
                        />
                    </Field>
                ) : (
                    <>
                        <Field label="Minecraft version">
                            {loadingList ? (
                                <SpinnerScreen label="Loading versions..." />
                            ) : (
                                <select style={inputStyle} value={mcVersion} onChange={(e) => changeVersion(e.target.value)}>
                                    <option value="">— select a version —</option>
                                    {versions.map((v) => (
                                        <option key={v} value={v}>
                                            {v}
                                        </option>
                                    ))}
                                </select>
                            )}
                        </Field>

                        {type !== 'spigot' && type !== 'vanilla' && (
                            <Field label="Build">
                                {loadingBuilds ? (
                                    <SpinnerScreen label="Loading builds..." />
                                ) : (
                                    <select
                                        style={inputStyle}
                                        value={build}
                                        onChange={(e) => changeBuild(e.target.value)}
                                        disabled={!mcVersion}
                                    >
                                        <option value="">{type === 'fabric' ? '— select a loader —' : 'latest build'}</option>
                                        {builds.map((b) => (
                                            <option key={b.build} value={b.build}>
                                                {b.build}
                                                {b.channel ? ` (${b.channel})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                )}
                            </Field>
                        )}
                    </>
                )}

                <Field label="Target file name">
                    <input style={inputStyle} value={jarName} onChange={(e) => setJarName(e.target.value)} spellCheck={false} />
                </Field>

                <button style={buttonStyle('primary')} onClick={install} disabled={installing}>
                    {installing ? 'Installing...' : 'Install version'}
                </button>
            </Card>

            <div style={{ height: 12 }} />

            <Card title="Jars in server root" right={<Badge>{jars ? jars.length : '—'}</Badge>}>
                {!jars ? (
                    <SpinnerScreen label="Listing server root..." />
                ) : jars.length === 0 ? (
                    <div style={{ color: colors.muted, fontSize: 13 }}>No .jar files in the server root.</div>
                ) : (
                    <Table head={['File', 'Size']}>
                        {jars.map((jar) => (
                            <tr key={jar.name} style={{ borderBottom: `1px solid ${colors.border}` }}>
                                <td style={{ padding: '8px 10px', color: colors.text, fontFamily: 'monospace', fontSize: 12 }}>
                                    {jar.name}
                                </td>
                                <td style={{ padding: '8px 10px', color: colors.muted }}>{fmtBytes(jar.size)}</td>
                            </tr>
                        ))}
                    </Table>
                )}
            </Card>
        </div>
    );
};

export default VersionsComponent;