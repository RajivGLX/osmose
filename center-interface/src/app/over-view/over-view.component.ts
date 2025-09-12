import {Component, ElementRef, OnInit, signal, ViewChild, WritableSignal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormGroup, FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatSelectModule} from '@angular/material/select';
import {RouterLink} from '@angular/router';
import {OverviewService} from './service/overview.service';
import {MatInputModule} from "@angular/material/input";
import {MatProgressSpinnerModule} from "@angular/material/progress-spinner";
import {MatCheckbox} from "@angular/material/checkbox";
import {Task} from "../interface/task.interface";
import {FormErrorService} from "../shared/services/forms-error.service";
import {FormErrorDirective} from "../shared/directives/form-error.directive";

@Component({
    selector: 'app-over-view',
    standalone: true,
    templateUrl: './over-view.component.html',
    styleUrl: './over-view.component.sass',
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        MatFormFieldModule,
        MatSelectModule,
        RouterLink,
        MatInputModule,
        MatProgressSpinnerModule,
        MatCheckbox,
        FormErrorDirective
    ]
})
export class OverViewComponent implements OnInit {
    switchView: 'overview' = 'overview';
    isTaskFormVisible = false;
    isLoading = false;
    loaderTasks: WritableSignal<boolean> = signal(false);
    tasks: Task[] = [];
    taskForm: FormGroup;
    @ViewChild('taskInput') taskInput!: ElementRef<HTMLInputElement>;

    constructor(
        private overViewService: OverviewService,
        private formErrorService: FormErrorService,
    ) {
        this.taskForm = this.overViewService.taskForm;
    }

    ngOnInit(): void {
        this.loadTasks();
    }

    changeView(view: 'overview') {
        this.switchView = view;
    }

    toggleTaskForm() {
        this.isTaskFormVisible = !this.isTaskFormVisible;
        if (this.isTaskFormVisible) {
            setTimeout(() => {
                this.taskInput?.nativeElement.focus();
            });
        }
    }

    loadTasks() {
        this.loaderTasks.set(true);
        this.overViewService.getTasks().subscribe(tasks => {
            // Filtrer pour ne garder que les tâches non cochées
            this.tasks = tasks.filter(task => !task.checked);
            this.loaderTasks.set(false);
        });
    }

    onSubmitTask() {
        if (this.taskForm.valid) {

            const description = this.taskForm.value.description;
            this.isLoading = true;

            this.overViewService.createTask(description).subscribe({
                next: () => {
                    this.isLoading = false;
                    this.taskForm.reset();
                    this.loadTasks();
                    this.isTaskFormVisible = false;
                },
                error: (error) => {
                    console.error('Erreur lors de la création de la tâche', error);
                    this.isLoading = false;
                }
            });
        } else {
            this.formErrorService.markFormGroupTouchedAndUpdate(this.taskForm);
        }
    }

    updateTaskStatus(task: any, checked: boolean) {
        if (!checked) return; // Ne rien faire si on décoche

        this.isLoading = true;

        this.overViewService.updateTaskStatus(task.id, checked).subscribe({
            next: () => {
                this.isLoading = false;

                // Trouver la tâche dans la liste
                const index = this.tasks.findIndex(t => t.id === task.id);
                if (index !== -1) {
                    // Mettre à jour l'état de la tâche
                    this.tasks[index].checked = checked;

                    // Ajouter une classe temporaire pour l'animation
                    setTimeout(() => {
                        // Ajouter une classe pour l'animation de sortie
                        const taskElement = document.querySelector(`[data-task-id="${task.id}"]`) as HTMLElement;
                        if (taskElement) {
                            taskElement.classList.add('fading-out');
                        }

                        // Supprimer l'élément après l'animation
                        setTimeout(() => {
                            this.tasks = this.tasks.filter(t => t.id !== task.id);
                        }, 500); // Même durée que l'animation CSS
                    }, 500); // Attendre un moment pour montrer la tâche cochée avant de la faire disparaître
                }
            },
            error: (error) => {
                console.error('Erreur lors de la mise à jour de la tâche', error);
                this.isLoading = false;
            }
        });
    }
}