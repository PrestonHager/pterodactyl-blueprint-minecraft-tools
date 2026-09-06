import React, { useEffect, useState } from 'react';
import { mtApi, mtUrl, useServerUuid } from './api';
import { Card, Badge, SpinnerScreen, Message, Field, buttonStyle, inputStyle, colors } from './ui';

const IconComponent: React.FC = () => {
    const server = useServerUuid();
    const [icon, setIcon] = useState<string | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const [text, setText] = useState('MC');
    const [background, setBackground] = useState('#2E86C1');
    const [fontColor, setFontColor] = useState('#FFFFFF');
    const [generating, setGenerating] = useState(false);
    const [imageUrl, setImageUrl] = useState('');
    const [uploading, setUploading] = useState(false);

    const load = () => {
        setLoading(true);
        setError(null);
        mtApi<{ icon: string | null }>(mtUrl(server, '/icon'))
            .then((d) => setIcon(d.icon || null))
            .catch((e: Error) => setError(e.message))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        if (server) load();
    }, [server]);

    const generate = async () => {
        setGenerating(true);
        setNotice(null);
        try {
            const d = await mtApi<{ icon: string | null }>(mtUrl(server, '/icon/generate'), {
                method: 'POST',
                body: { text, background, font_color: fontColor },
            });
            setIcon(d.icon || null);
            setNotice('Generated and saved to server-icon.png on the server.');
        } catch (e) {
            setNotice('Generate failed: ' + (e as Error).message);
        } finally {
            setGenerating(false);
        }
    };

    const upload = async () => {
        if (!imageUrl.trim()) return;
        setUploading(true);
        setNotice(null);
        try {
            const d = await mtApi<{ icon: string }>(mtUrl(server, '/icon/upload'), {
                method: 'POST',
                body: { image_url: imageUrl.trim() },
            });
            setIcon(d.icon || null);
            setNotice('Uploaded and saved to server-icon.png on the server.');
            setImageUrl('');
        } catch (e) {
            setNotice('Upload failed: ' + (e as Error).message);
        } finally {
            setUploading(false);
        }
    };

    const remove = async () => {
        if (!window.confirm('Delete server-icon.png from this server?')) return;
        setNotice(null);
        try {
            await mtApi(mtUrl(server, '/icon'), { method: 'DELETE' });
            setIcon(null);
            setNotice('Removed the server icon.');
        } catch (e) {
            setNotice('Remove failed: ' + (e as Error).message);
        }
    };

    return (
        <div>
            {notice && <Message tone={notice.includes('failed') ? 'error' : 'success'}>{notice}</Message>}
            {error && <Message tone="error">{error}</Message>}

            <div style={{ marginBottom: 12 }}>
                <h2 style={{ color: colors.text, margin: 0 }}>Server Icon</h2>
                <div style={{ color: colors.muted, fontSize: 13, marginTop: 4 }}>
                    Reads and writes server-icon.png directly on this server.
                </div>
            </div>

            <Card title="Current icon" right={<Badge color={icon ? colors.success : colors.muted}>{icon ? 'set' : 'none'}</Badge>}>
                {loading ? (
                    <SpinnerScreen label="Reading server-icon.png..." />
                ) : icon ? (
                    <img
                        src={icon}
                        alt="Server icon"
                        style={{
                            width: 128,
                            height: 128,
                            borderRadius: 8,
                            border: `2px solid ${colors.border}`,
                            imageRendering: 'pixelated',
                            backgroundColor: colors.panel,
                        }}
                    />
                ) : (
                    <div style={{ color: colors.muted, fontSize: 13 }}>
                        This server has no server-icon.png yet. Generate one below (a restart is needed before the icon is
                        shown to players).
                    </div>
                )}
                {icon && (
                    <div style={{ marginTop: 16 }}>
                        <button style={buttonStyle('danger')} onClick={remove}>
                            Remove icon
                        </button>
                    </div>
                )}
            </Card>

            <div style={{ height: 12 }} />

            <Card title="Generate an icon">
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
                    <Field label="Text (max 4 chars)">
                        <input
                            style={inputStyle}
                            value={text}
                            onChange={(e) => setText(e.target.value)}
                            maxLength={4}
                            spellCheck={false}
                        />
                    </Field>
                    <Field label="Background">
                        <input
                            style={inputStyle}
                            type="color"
                            value={background}
                            onChange={(e) => setBackground(e.target.value)}
                        />
                    </Field>
                    <Field label="Font color">
                        <input
                            style={inputStyle}
                            type="color"
                            value={fontColor}
                            onChange={(e) => setFontColor(e.target.value)}
                        />
                    </Field>
                </div>
                <button style={buttonStyle('primary')} onClick={generate} disabled={generating}>
                    {generating ? 'Generating...' : 'Generate 64x64 icon'}
                </button>
            </Card>

            <div style={{ height: 12 }} />

            <Card title="Upload from a URL">
                <Field label="Image URL" hint="Must start with http(s):// and return an image (max 2MB).">
                    <div style={{ display: 'flex', gap: 8 }}>
                        <input
                            style={inputStyle}
                            value={imageUrl}
                            onChange={(e) => setImageUrl(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && upload()}
                            placeholder="https://example.com/icon.png"
                            spellCheck={false}
                        />
                        <button style={buttonStyle()} onClick={upload} disabled={uploading}>
                            {uploading ? 'Uploading...' : 'Upload'}
                        </button>
                    </div>
                </Field>
            </Card>
        </div>
    );
};

export default IconComponent;