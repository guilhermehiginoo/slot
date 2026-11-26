import { Form } from '@inertiajs/react';
import ClassMonitorController from '@/actions/App/Http/Controllers/Professor/ClassMonitorController';
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
import type { ClassMonitor } from '@/types';

type Props = {
    classGroupId: number;
    monitor: ClassMonitor;
};

export function RemoveMonitorDialog({ classGroupId, monitor }: Props) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm" className="h-11">
                    Remover
                </Button>
            </DialogTrigger>
            <DialogContent className="bg-card">
                <DialogTitle>Remover {monitor.name} da monitoria?</DialogTitle>
                <DialogDescription>
                    {monitor.name} deixa de ser monitor desta turma. Para
                    voltar, precisará de um novo convite.
                </DialogDescription>
                <Form
                    {...ClassMonitorController.destroy.form({
                        classGroup: classGroupId,
                        monitor: monitor.id,
                    })}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="outline">Cancelar</Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                Remover monitor
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
