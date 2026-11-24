import type { ReactNode } from 'react';

export type PersonRow = {
    id: number;
    name: string;
    details: ReactNode[];
};

type Props = {
    title: string;
    columns: string[];
    rows: PersonRow[];
    emptyMessage: string;
};

export function PeopleSection({ title, columns, rows, emptyMessage }: Props) {
    return (
        <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">{title}</h2>

            {rows.length === 0 && (
                <p className="rounded-xl border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
                    {emptyMessage}
                </p>
            )}

            {rows.length > 0 && (
                <>
                    <ul className="grid gap-3 md:hidden">
                        {rows.map((row) => (
                            <li
                                key={row.id}
                                className="flex flex-col gap-1 rounded-xl border bg-card p-4"
                            >
                                <span className="font-semibold">
                                    {row.name}
                                </span>
                                {row.details.map((detail, index) => (
                                    <p key={columns[index]} className="text-sm">
                                        <span className="text-muted-foreground">
                                            {columns[index]}:
                                        </span>{' '}
                                        {detail}
                                    </p>
                                ))}
                            </li>
                        ))}
                    </ul>

                    <div className="hidden overflow-hidden rounded-xl border bg-card md:block">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left text-xs text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-4 py-3 font-medium">
                                        Aluno
                                    </th>
                                    {columns.map((column) => (
                                        <th
                                            key={column}
                                            className="px-4 py-3 font-medium"
                                        >
                                            {column}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {rows.map((row) => (
                                    <tr key={row.id}>
                                        <td className="px-4 py-4 font-semibold">
                                            {row.name}
                                        </td>
                                        {row.details.map((detail, index) => (
                                            <td
                                                key={columns[index]}
                                                className="px-4 py-4"
                                            >
                                                {detail}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </section>
    );
}
