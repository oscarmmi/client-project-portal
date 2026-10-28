<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    project: { type: Object, default: null },
    clients: { type: Array, required: true },
    milestoneStatuses: { type: Array, required: true },
});

const projectForm = useForm({
    client_user_id: props.project?.client_user_id ?? '',
    title: props.project?.title ?? '',
    description: props.project?.description ?? '',
});

const milestoneForm = useForm({
    title: '',
    description: '',
    status: 'pending',
    due_date: '',
});

const updateForm = useForm({ body: '' });
const milestoneForms = reactive({});

function saveProject() {
    if (props.project) {
        projectForm.patch(route('owner.project.update', props.project.id));
        return;
    }

    projectForm.post(route('owner.project.store'));
}

function milestoneFormFor(milestone) {
    if (!milestoneForms[milestone.id]) {
        milestoneForms[milestone.id] = useForm({
            title: milestone.title,
            description: milestone.description ?? '',
            status: milestone.status,
            due_date: milestone.due_date?.slice(0, 10) ?? '',
            sort_order: milestone.sort_order,
        });
    }

    return milestoneForms[milestone.id];
}

function saveMilestone(milestone) {
    milestoneFormFor(milestone).patch(route('owner.milestones.update', [props.project.id, milestone.id]), {
        onSuccess: () => delete milestoneForms[milestone.id],
    });
}

function deleteProject() {
    if (window.confirm('Delete this project and all of its milestones and updates?')) {
        router.delete(route('owner.project.destroy', props.project.id));
    }
}

function addMilestone() {
    milestoneForm.post(route('owner.milestones.store', props.project.id), {
        onSuccess: () => milestoneForm.reset('title', 'description', 'due_date'),
    });
}

function postUpdate() {
    updateForm.post(route('owner.updates.store', props.project.id), {
        onSuccess: () => updateForm.reset(),
    });
}

function formatTimestamp(timestamp) {
    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(timestamp));
}
</script>

<template>
    <Head title="Manage project" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Project management</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <section class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <h1 class="text-lg font-semibold text-gray-900">{{ project ? 'Project details' : 'Create your project' }}</h1>
                        <p class="mt-1 text-sm text-gray-600">Keep the client and project information up to date.</p>
                    </div>

                    <form class="max-w-2xl space-y-5" @submit.prevent="saveProject">
                        <div>
                            <InputLabel for="client_user_id" value="Client" />
                            <select id="client_user_id" v-model="projectForm.client_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" :required="clients.length > 0">
                                <option value="" disabled>{{ clients.length ? 'Select a client' : 'No client accounts available' }}</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} ({{ client.email }})</option>
                            </select>
                            <InputError class="mt-2" :message="projectForm.errors.client_user_id" />
                            <p v-if="!clients.length" class="mt-2 text-sm text-gray-600">There are no client accounts yet. <a :href="route('register')" class="font-medium text-indigo-700 underline hover:text-indigo-900">Create a client account</a> before setting up a project.</p>
                        </div>

                        <div>
                            <InputLabel for="title" value="Project name" />
                            <TextInput id="title" v-model="projectForm.title" type="text" class="mt-1 block w-full" required maxlength="255" />
                            <InputError class="mt-2" :message="projectForm.errors.title" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Project overview" />
                            <textarea id="description" v-model="projectForm.description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError class="mt-2" :message="projectForm.errors.description" />
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <PrimaryButton :disabled="projectForm.processing || (!project && !clients.length)">{{ project ? 'Save project' : 'Create project' }}</PrimaryButton>
                            <button v-if="project" type="button" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteProject">Delete project</button>
                        </div>
                    </form>
                </section>

                <section v-if="project" class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Milestones</h2>
                        <p class="mt-1 text-sm text-gray-600">Add delivery steps and keep their status current.</p>
                    </div>

                    <div v-if="project.milestones.length" class="mb-8 divide-y divide-gray-200">
                        <form v-for="milestone in project.milestones" :key="milestone.id" class="grid gap-4 py-5 md:grid-cols-2" @submit.prevent="saveMilestone(milestone)">
                            <div>
                                <InputLabel :for="`milestone-title-${milestone.id}`" value="Milestone" />
                                <TextInput :id="`milestone-title-${milestone.id}`" v-model="milestoneFormFor(milestone).title" type="text" class="mt-1 block w-full" required maxlength="255" />
                                <InputError class="mt-2" :message="milestoneFormFor(milestone).errors.title" />
                            </div>
                            <div>
                                <InputLabel :for="`milestone-status-${milestone.id}`" value="Status" />
                                <select :id="`milestone-status-${milestone.id}`" v-model="milestoneFormFor(milestone).status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="status in milestoneStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="milestoneFormFor(milestone).errors.status" />
                            </div>
                            <div>
                                <InputLabel :for="`milestone-description-${milestone.id}`" value="Details" />
                                <textarea :id="`milestone-description-${milestone.id}`" v-model="milestoneFormFor(milestone).description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <InputError class="mt-2" :message="milestoneFormFor(milestone).errors.description" />
                            </div>
                            <div class="flex items-end gap-3">
                                <div class="min-w-0 flex-1">
                                    <InputLabel :for="`milestone-due-${milestone.id}`" value="Due date" />
                                    <input :id="`milestone-due-${milestone.id}`" v-model="milestoneFormFor(milestone).due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <PrimaryButton type="submit">Save</PrimaryButton>
                                <button type="button" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="router.delete(route('owner.milestones.destroy', [project.id, milestone.id]))">Remove</button>
                            </div>
                        </form>
                    </div>
                    <p v-else class="mb-8 rounded-md bg-gray-50 p-4 text-sm text-gray-600">No milestones yet. Add the first delivery step below.</p>

                    <form class="max-w-2xl space-y-5 border-t border-gray-200 pt-6" @submit.prevent="addMilestone">
                        <h3 class="font-medium text-gray-900">Add a milestone</h3>
                        <div>
                            <InputLabel for="new-milestone-title" value="Milestone name" />
                            <TextInput id="new-milestone-title" v-model="milestoneForm.title" type="text" class="mt-1 block w-full" required maxlength="255" />
                            <InputError class="mt-2" :message="milestoneForm.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="new-milestone-description" value="Details" />
                            <textarea id="new-milestone-description" v-model="milestoneForm.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError class="mt-2" :message="milestoneForm.errors.description" />
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <InputLabel for="new-milestone-status" value="Status" />
                                <select id="new-milestone-status" v-model="milestoneForm.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="status in milestoneStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                                </select>
                                <InputError class="mt-2" :message="milestoneForm.errors.status" />
                            </div>
                            <div>
                                <InputLabel for="new-milestone-due" value="Due date" />
                                <input id="new-milestone-due" v-model="milestoneForm.due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <InputError class="mt-2" :message="milestoneForm.errors.due_date" />
                            </div>
                        </div>
                        <PrimaryButton :disabled="milestoneForm.processing">Add milestone</PrimaryButton>
                    </form>
                </section>

                <section v-if="project" class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Project updates</h2>
                        <p class="mt-1 text-sm text-gray-600">Share a progress note with the client.</p>
                    </div>

                    <form class="mb-8 max-w-2xl space-y-4" @submit.prevent="postUpdate">
                        <div>
                            <InputLabel for="project-update" value="New update" />
                            <textarea id="project-update" v-model="updateForm.body" rows="4" maxlength="10000" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Share what has changed or what is coming next." />
                            <InputError class="mt-2" :message="updateForm.errors.body" />
                        </div>
                        <PrimaryButton :disabled="updateForm.processing">Post update</PrimaryButton>
                    </form>

                    <div v-if="project.updates.length" class="space-y-5 border-t border-gray-200 pt-6">
                        <article v-for="update in project.updates" :key="update.id" class="border-l-2 border-indigo-200 pl-4">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <p class="text-sm font-medium text-gray-900">{{ update.author.name }}</p>
                                <time class="text-xs text-gray-500" :datetime="update.created_at">{{ formatTimestamp(update.created_at) }}</time>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700">{{ update.body }}</p>
                        </article>
                    </div>
                    <p v-else class="border-t border-gray-200 pt-6 text-sm text-gray-600">No updates yet. Post a note when work begins.</p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
