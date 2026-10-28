<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    project: { type: Object, default: null },
});

const completedCount = computed(() => props.project?.milestones.filter((milestone) => milestone.status === 'done').length ?? 0);
const milestoneCount = computed(() => props.project?.milestones.length ?? 0);
const progress = computed(() => milestoneCount.value === 0 ? 0 : Math.round((completedCount.value / milestoneCount.value) * 100));
const nextMilestone = computed(() => props.project?.milestones.find((milestone) => milestone.status !== 'done') ?? null);

function formatDate(value) {
    if (!value) return null;

    return new Intl.DateTimeFormat('en', { dateStyle: 'medium' }).format(new Date(value));
}

function formatTimestamp(value) {
    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function statusLabel(status) {
    return {
        pending: 'Pending',
        in_progress: 'In progress',
        done: 'Complete',
    }[status] ?? status;
}

function statusClasses(status) {
    return {
        pending: 'bg-gray-100 text-gray-700',
        in_progress: 'bg-blue-100 text-blue-800',
        done: 'bg-green-100 text-green-800',
    }[status] ?? 'bg-gray-100 text-gray-700';
}
</script>

<template>
    <Head :title="project ? project.title : 'Your project'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Your project</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section v-if="project" class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-sm font-medium uppercase tracking-wide text-indigo-700">Project overview</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900">{{ project.title }}</h1>
                    <p v-if="project.description" class="mt-3 max-w-3xl whitespace-pre-line leading-7 text-gray-600">{{ project.description }}</p>

                    <div class="mt-8">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="font-medium text-gray-900">Overall progress</h2>
                            <span class="text-sm font-semibold text-gray-700">{{ progress }}%</span>
                        </div>
                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" :aria-label="`${progress}% of project milestones complete`">
                            <div class="h-full rounded-full bg-indigo-600 transition-all" :style="{ width: `${progress}%` }" />
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ completedCount }} of {{ milestoneCount }} milestones complete</p>
                    </div>

                    <div v-if="nextMilestone" class="mt-8 rounded-md bg-indigo-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Next delivery</p>
                        <p class="mt-1 font-medium text-gray-900">{{ nextMilestone.title }}</p>
                        <p v-if="nextMilestone.due_date" class="mt-1 text-sm text-gray-600">Target date: {{ formatDate(nextMilestone.due_date) }}</p>
                    </div>
                </section>

                <section v-if="project" class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Milestones</h2>
                            <p class="mt-1 text-sm text-gray-600">A clear view of each step in the project.</p>
                        </div>
                        <span class="text-sm text-gray-500">{{ milestoneCount }} total</span>
                    </div>

                    <ol v-if="project.milestones.length" class="space-y-5">
                        <li v-for="(milestone, index) in project.milestones" :key="milestone.id" class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold" :class="milestone.status === 'done' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">{{ milestone.status === 'done' ? '✓' : index + 1 }}</span>
                                <span v-if="index < project.milestones.length - 1" class="mt-2 h-full min-h-6 w-px bg-gray-200" />
                            </div>
                            <div class="min-w-0 flex-1 pb-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="font-medium text-gray-900">{{ milestone.title }}</h3>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClasses(milestone.status)">{{ statusLabel(milestone.status) }}</span>
                                </div>
                                <p v-if="milestone.description" class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600">{{ milestone.description }}</p>
                                <p v-if="milestone.due_date" class="mt-2 text-xs text-gray-500">Target: {{ formatDate(milestone.due_date) }}</p>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">Milestones will appear here as the project plan takes shape.</p>
                </section>

                <section v-if="project" class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Latest updates</h2>
                        <p class="mt-1 text-sm text-gray-600">Notes from the project owner, newest first.</p>
                    </div>

                    <div v-if="project.updates.length" class="space-y-6">
                        <article v-for="update in project.updates" :key="update.id" class="border-l-2 border-indigo-200 pl-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <p class="text-sm font-medium text-gray-900">{{ update.author.name }}</p>
                                <time class="text-xs text-gray-500" :datetime="update.created_at">{{ formatTimestamp(update.created_at) }}</time>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">{{ update.body }}</p>
                        </article>
                    </div>
                    <p v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">There are no updates yet.</p>
                </section>

                <section v-else class="rounded-lg bg-white p-8 text-center shadow-sm">
                    <h1 class="text-lg font-semibold text-gray-900">Your project is being set up</h1>
                    <p class="mt-2 text-sm text-gray-600">The project overview will be available here as soon as it is ready.</p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
