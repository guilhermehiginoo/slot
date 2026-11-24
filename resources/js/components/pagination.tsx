import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

type Props = {
    page: Pick<
        Paginated<unknown>,
        'current_page' | 'last_page' | 'prev_page_url' | 'next_page_url'
    >;
};

export function Pagination({ page }: Props) {
    if (page.last_page <= 1) {
        return null;
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
}: {
    href: string | null;
    children: React.ReactNode;
}) {
    if (href === null) {
        return (
            <Button variant="outline" size="sm" disabled>
                {children}
            </Button>
        );
    }

    return (
        <Button variant="outline" size="sm" asChild>
            <Link href={href} preserveState>
                {children}
            </Link>
        </Button>
    );
}
