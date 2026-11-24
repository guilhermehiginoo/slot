import { Check } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { ProfessorOption } from '@/types';

type Props = {
    id: string;
    professors: ProfessorOption[];
    selected: number[];
    invalid: boolean;
    onApply: (selected: number[]) => void;
};

export function ProfessorPickerDialog({
    id,
    professors,
    selected,
    invalid,
    onApply,
}: Props) {
    const [open, setOpen] = useState(false);
    const [draft, setDraft] = useState<number[]>([]);
    const [search, setSearch] = useState('');

    const openPicker = (isOpen: boolean) => {
        if (isOpen) {
            setDraft(selected);
            setSearch('');
        }

        setOpen(isOpen);
    };

    const toggle = (professorId: number) =>
        setDraft((current) =>
            current.includes(professorId)
                ? current.filter((id) => id !== professorId)
                : [...current, professorId],
        );

    const apply = () => {
        onApply(draft);
        setOpen(false);
    };

    const selectedNames = professors
        .filter((professor) => selected.includes(professor.id))
        .map((professor) => professor.name)
        .join(' + ');

    const term = search.trim().toLocaleLowerCase('pt-BR');
    const visible = professors.filter((professor) =>
        professor.name.toLocaleLowerCase('pt-BR').includes(term),
    );

    return (
        <Dialog open={open} onOpenChange={openPicker}>
            <DialogTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    aria-invalid={invalid}
                    className={cn(
                        'h-auto min-h-11 w-full justify-start text-left font-normal whitespace-normal',
                        selectedNames === '' && 'text-muted-foreground',
                    )}
                >
                    {selectedNames || 'Selecione um ou mais professores'}
                </Button>
            </DialogTrigger>

            <DialogContent className="max-h-[calc(100dvh-2rem)] overflow-y-auto bg-card">
                <DialogTitle>Selecionar professores</DialogTitle>
                <DialogDescription>
                    Somente professores aparecem nesta lista. Selecione uma ou
                    mais pessoas para esta turma.
                </DialogDescription>

                <div className="grid gap-2">
                    <Label htmlFor="professor-search" className="sr-only">
                        Buscar professor pelo nome
                    </Label>
                    <Input
                        id="professor-search"
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Buscar pelo nome"
                        className="h-11"
                    />
                </div>

                <ul className="grid max-h-72 gap-2 overflow-y-auto">
                    {visible.map((professor) => {
                        const isSelected = draft.includes(professor.id);

                        return (
                            <li key={professor.id}>
                                <button
                                    type="button"
                                    aria-pressed={isSelected}
                                    onClick={() => toggle(professor.id)}
                                    className={cn(
                                        'flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left text-sm transition-colors hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
                                        isSelected &&
                                            'border-primary bg-primary/5',
                                    )}
                                >
                                    {professor.name}
                                    {isSelected && (
                                        <Check className="size-4 text-primary" />
                                    )}
                                </button>
                            </li>
                        );
                    })}
                    {visible.length === 0 && (
                        <li className="py-4 text-center text-sm text-muted-foreground">
                            Nenhum professor encontrado.
                        </li>
                    )}
                </ul>

                <DialogFooter className="gap-2">
                    <DialogClose asChild>
                        <Button type="button" variant="outline">
                            Cancelar
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={apply}>
                        Aplicar seleção
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
