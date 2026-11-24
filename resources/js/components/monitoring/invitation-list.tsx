import { Form } from '@inertiajs/react';
import MonitorInvitationController from '@/actions/App/Http/Controllers/MonitorInvitationController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import type { StudentInvitation } from '@/types';

export function InvitationList({
    invitations,
}: {
    invitations: StudentInvitation[];
}) {
    return (
        <section className="flex flex-col gap-3">
            <div className="space-y-1">
                <h2 className="text-xl font-semibold">Convites de monitoria</h2>
                <p className="text-sm text-muted-foreground">
                    Ao aceitar, seu nome e seu perfil passam a aparecer para os
                    alunos da disciplina.
                </p>
            </div>

            <ul className="grid gap-3 md:grid-cols-2">
                {invitations.map((invitation) => (
                    <li
                        key={invitation.id}
                        className="flex flex-col gap-3 rounded-xl border bg-card p-4"
                    >
                        <div className="space-y-1">
                            <p className="font-semibold">
                                {invitation.classGroup.course.code} ·{' '}
                                {invitation.classGroup.number}
                            </p>
                            <p className="text-sm">
                                {invitation.classGroup.course.name}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Convite de {invitation.invitedBy} em{' '}
                                {formatDate(invitation.created_at)}
                            </p>
                        </div>

                        <Form
                            {...MonitorInvitationController.update.form(
                                invitation.id,
                            )}
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-2"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            name="status"
                                            value="accepted"
                                            className="h-11 flex-1"
                                            disabled={processing}
                                        >
                                            Aceitar
                                        </Button>
                                        <Button
                                            type="submit"
                                            name="status"
                                            value="declined"
                                            variant="outline"
                                            className="h-11 flex-1"
                                            disabled={processing}
                                        >
                                            Recusar
                                        </Button>
                                    </div>
                                    <InputError
                                        message={
                                            errors.invitation ?? errors.status
                                        }
                                    />
                                </>
                            )}
                        </Form>
                    </li>
                ))}
            </ul>
        </section>
    );
}
