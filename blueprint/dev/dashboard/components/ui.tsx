import React from 'react';

export const colors = {
    bg: '#16181d',
    card: '#1e2128',
    panel: '#262a33',
    border: '#3a3f4b',
    text: '#e5e7eb',
    muted: '#9aa2b1',
    accent: '#3b82f6',
    accentHover: '#2563eb',
    danger: '#ef4444',
    success: '#22c55e',
    warn: '#f59e0b',
};

export const inputStyle: React.CSSProperties = {
    backgroundColor: '#101216',
    border: `1px solid ${colors.border}`,
    color: colors.text,
    borderRadius: 6,
    padding: '8px 10px',
    fontSize: 14,
    width: '100%',
    boxSizing: 'border-box',
};

export const buttonStyle = (kind: 'default' | 'primary' | 'danger' | 'success' = 'default'): React.CSSProperties => {
    const base: React.CSSProperties = {
        border: 'none',
        borderRadius: 6,
        padding: '8px 14px',
        fontSize: 13,
        fontWeight: 600,
        cursor: 'pointer',
        color: '#fff',
        marginRight: 6,
    };
    switch (kind) {
        case 'primary':
            return { ...base, backgroundColor: colors.accent };
        case 'danger':
            return { ...base, backgroundColor: colors.danger };
        case 'success':
            return { ...base, backgroundColor: colors.success };
        default:
            return { ...base, backgroundColor: colors.panel, color: colors.text, border: `1px solid ${colors.border}` };
    }
};

export const Card: React.FC<{ title?: string; right?: React.ReactNode; children: React.ReactNode }> = ({
    title,
    right,
    children,
}) => (
    <div style={{ backgroundColor: colors.card, border: `1px solid ${colors.border}`, borderRadius: 8, overflow: 'hidden' }}>
        {title && (
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    padding: '10px 16px',
                    borderBottom: `1px solid ${colors.border}`,
                    backgroundColor: colors.panel,
                }}
            >
                <span style={{ fontSize: 14, fontWeight: 700, color: colors.text }}>{title}</span>
                {right}
            </div>
        )}
        <div style={{ padding: 16 }}>{children}</div>
    </div>
);

export const Badge: React.FC<{ color?: string; children: React.ReactNode }> = ({ color = colors.success, children }) => (
    <span
        style={{
            display: 'inline-block',
            fontSize: 11,
            fontWeight: 700,
            color: '#fff',
            backgroundColor: color,
            borderRadius: 999,
            padding: '2px 8px',
        }}
    >
        {children}
    </span>
);

export const Message: React.FC<{ tone?: 'error' | 'success' | 'info'; children: React.ReactNode }> = ({
    tone = 'info',
    children,
}) => {
    const bg = tone === 'error' ? 'rgba(239,68,68,.12)' : tone === 'success' ? 'rgba(34,197,94,.12)' : 'rgba(59,130,246,.12)';
    const color = tone === 'error' ? colors.danger : tone === 'success' ? colors.success : colors.accent;
    return (
        <div style={{ backgroundColor: bg, color, borderRadius: 6, padding: '10px 12px', fontSize: 13, marginBottom: 12 }}>
            {children}
        </div>
    );
};

export const SpinnerScreen: React.FC<{ label?: string }> = ({ label }) => (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', padding: 40, flexDirection: 'column' }}>
        <style>{`@keyframes mt-spin { to { transform: rotate(360deg); } }`}</style>
        <div
            style={{
                width: 40,
                height: 40,
                border: '4px solid rgba(255,255,255,.15)',
                borderTopColor: colors.accent,
                borderRadius: '50%',
                animation: 'mt-spin 1s linear infinite',
            }}
        />
        {label && <div style={{ color: colors.muted, marginTop: 12, fontSize: 13 }}>{label}</div>}
    </div>
);

export const Field: React.FC<{ label: string; hint?: string; children: React.ReactNode }> = ({ label, hint, children }) => (
    <div style={{ marginBottom: 12 }}>
        <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: colors.text, marginBottom: 6 }}>{label}</label>
        {children}
        {hint && <div style={{ fontSize: 12, color: colors.muted, marginTop: 4 }}>{hint}</div>}
    </div>
);

export const Table: React.FC<{
    head: string[];
    children: React.ReactNode;
    empty?: React.ReactNode;
}> = ({ head, children, empty }) => (
    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
        <thead>
            <tr>
                {head.map((h) => (
                    <th
                        key={h}
                        style={{
                            textAlign: 'left',
                            padding: '8px 10px',
                            color: colors.muted,
                            borderBottom: `1px solid ${colors.border}`,
                            fontSize: 12,
                            textTransform: 'uppercase',
                            letterSpacing: 0.4,
                        }}
                    >
                        {h}
                    </th>
                ))}
            </tr>
        </thead>
        <tbody>{children}</tbody>
    </table>
);