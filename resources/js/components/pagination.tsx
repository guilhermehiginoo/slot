import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

type Props = {
    page: Pick<
        Paginated<unknown>,
        | 'current_page'
        | 'last_page'
        | 'prev_page_url'
        | 'next_page_url'
        | 'from'
        | 'to'
        | 'total'
    >;
    label?: string;
};

export function Pagination({ page, label }: Props) {
    if (page.total === 0 || (label === undefined && page.last_page <= 1)) {
        return null;
    }

    if (label !== undefined) {
        return (
            <nav
                aria-label="Paginação"
                className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
                <span className="text-sm text-muted-foreground">
                    Exibindo {page.from}–{page.to} de {page.total} {label}
                </span>
                <div className="grid grid-cols-2 gap-2">
                    <PageButton href={page.prev_page_url} size="lg">
                        Anterior
                    </PageButton>
                    <PageButton href={page.next_page_url} size="lg">
                        Próximo
                    </PageButton>
                </div>
            </nav>
        );
    }

    return (
        <nav
            aria-label="Paginação"
            className="flex items-center justify-between gap-2"
        >
            <PageButton href={page.prev_page_url}>
                <ChevronLeft />
                Anterior
            </PageButton>

            <span className="text-sm text-muted-foreground">
                Página {page.current_page} de {page.last_page}
            </span>

            <PageButton href={page.next_page_url}>
                Próxima
                <ChevronRight />
            </PageButton>
        </nav>
    );
}

function PageButton({
    href,
    children,
    size = 'sm',
}: {
    href: string | null;
    children: React.ReactNode;
    size?: 'sm' | 'lg';
}) {
    if (href === null) {
        return (
            <Button variant="outline" size={size} disabled>
                {children}
            </Button>
        );
    }

    return (
        <Button variant="outline" size={size} asChild>
            <Link href={href} preserveState>
                {children}
            </Link>
        </Button>
    );
}
