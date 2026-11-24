import type { ReactNode } from 'react';

type Props = {
    title: string;
    description: string;
    icon?: ReactNode;
    action?: ReactNode;
};

export function EmptyState({ title, description, icon, action }: Props) {
    return (
        <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed px-4 py-12 text-center">
            {icon}
            <p className="font-medium">{title}</p>
            <p className="text-sm text-muted-foreground">{description}</p>
            {action && <div className="mt-2">{action}</div>}
        </div>
    );
}
