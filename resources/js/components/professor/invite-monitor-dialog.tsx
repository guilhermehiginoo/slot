import { Form, router } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
import ClassGroupController from '@/actions/App/Http/Controllers/Professor/ClassGroupController';
import MonitorInvitationController from '@/actions/App/Http/Controllers/Professor/MonitorInvitationController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { MonitorCandidate } from '@/types';

type Props = {
    classGroupId: number;
    search: string;
    candidates: MonitorCandidate[];
};

export function InviteMonitorDialog({
    classGroupId,
    search,
    candidates,
}: Props) {
    const [open, setOpen] = useState(false);
    const [term, setTerm] = useState(search);

    useEffect(() => {
        if (!open || term.trim() === search) {
            return;
        }

        const timeout = setTimeout(
            () =>
                router.get(
                    ClassGroupController.show.url(classGroupId, {
                        query: { busca: term.trim() || undefined },
                    }),
                    {},
                    {
                        only: ['candidates', 'search'],
                        preserveState: true,
                        preserveScroll: true,
                        replace: true,
                    },
                ),
            300,
        );

        return () => clearTimeout(timeout);
    }, [open, term, search, classGroupId]);

    const showResults = term.trim().length >= 2 && term.trim() === search;

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button className="h-11 md:self-start">
                    <UserPlus />
                    Convidar aluno
                </Button>
            </DialogTrigger>

            <DialogContent className="max-h-[calc(100dvh-2rem)] overflow-y-auto bg-card">
                <DialogTitle>Convidar aluno para a monitoria</DialogTitle>
                <DialogDescription>
                    O aluno precisa aceitar o convite para virar monitor. Ao
                    aceitar, o nome dele passa a aparecer para os alunos.
                </DialogDescription>

                <div className="grid gap-2">
                    <Label htmlFor="candidate-search">
                        Buscar aluno pelo nome ou e-mail
                    </Label>
                    <Input
                        id="candidate-search"
                        type="search"
                        value={term}
                        onChange={(event) => setTerm(event.target.value)}
                        placeholder="Digite ao menos 2 letras"
                        className="h-11"
                        autoComplete="off"
                    />
                </div>

                {showResults && (
                    <ul className="grid max-h-72 gap-2 overflow-y-auto">
                        {candidates.map((candidate) => (
                            <li
                                key={candidate.id}
                                className="flex items-center justify-between gap-3 rounded-lg border px-3 py-2"
                            >
                                <div className="min-w-0 text-sm">
                                    <p className="truncate font-medium">
                                        {candidate.name}
                                    </p>
                                    <p className="truncate text-muted-foreground">
                                        {candidate.email}
                                    </p>
                                </div>
                                <Form
                                    {...MonitorInvitationController.store.form(
                                        classGroupId,
                                    )}
                                    options={{ preserveScroll: true }}
                                    onSuccess={() => {
                                        setOpen(false);
                                        setTerm('');
                                    }}
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value={candidate.id}
                                            />
                                            <Button
                                                type="submit"
                                                size="sm"
                                                className="h-11"
                                                disabled={processing}
                                            >
                                                Convidar
                                            </Button>
                                            <InputError
                                                message={errors.user_id}
                                            />
                                        </>
                                    )}
                                </Form>
                            </li>
                        ))}
                        {candidates.length === 0 && (
                            <li className="py-4 text-center text-sm text-muted-foreground">
                                Nenhum aluno encontrado.
                            </li>
                        )}
                    </ul>
                )}
            </DialogContent>
        </Dialog>
    );
}
