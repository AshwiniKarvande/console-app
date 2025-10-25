import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type AmcType } from '@/types';
import { Head } from '@inertiajs/react';

import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Amc',
        href: dashboard().url,
    },
];


export default function Amc({ amcs }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Amc" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <Table>
                        <TableCaption>Asset management compny list</TableCaption>
                        <TableHeader>
                            <TableRow>
                            <TableHead className="w-[100px]">Code</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead className="text-right">AUM</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {amcs.map((amc: AmcType) => (
                            <TableRow key={amc.id}>
                                <TableCell className="font-medium">{amc.id}</TableCell>
                                <TableCell>{amc.name}</TableCell>
                                <TableCell>ACTIVE</TableCell>
                                <TableCell className="text-right">NA</TableCell>
                            </TableRow>
                            ))}
                        </TableBody>
                        <TableFooter>
                            <TableRow>
                            <TableCell colSpan={4}>Data from amphiindia.com</TableCell>
                            </TableRow>
                        </TableFooter>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
