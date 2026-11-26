import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

type Props = {
    page: Pick<
        Paginated<unknown>,
        'prev_page_url' | 'next_page_url' | 'from' | 'to' | 'total'
    >;
    label: string;
};

export function Pagination({ page, label }: Props) {
    if (page.total === 0) {
        return null;
    }

    return (
        <nav
            aria-label="Paginação"
            className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
        >
            <span className="text-sm text-muted-foreground">
                Exibindo {page.from}–{page.to} de {page.total} {label}
            </span>
            <div className="grid grid-cols-2 gap-2">
                <PageButton href={page.prev_page_url}>Anterior</PageButton>
                <PageButton href={page.next_page_url}>Próximo</PageButton>
            </div>
        </nav>
    );
}

function PageButton({
    href,
    children,
}: {
    href: string | null;
    children: React.ReactNode;
}) {
    if (href === null) {
        return (
            <Button variant="outline" size="lg" disabled>
                {children}
            </Button>
        );
    }

    return (
        <Button variant="outline" size="lg" asChild>
            <Link href={href} preserveState>
                {children}
            </Link>
        </Button>
    );
}
