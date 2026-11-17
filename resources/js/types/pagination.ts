export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** The JSON of Laravel's LengthAwarePaginator. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};
