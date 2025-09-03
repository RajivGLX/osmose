import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CellActionUserActiveComponent } from './cell-action-user-active.component';

describe('ActionCellRendererComponent', () => {
  let component: CellActionUserActiveComponent;
  let fixture: ComponentFixture<CellActionUserActiveComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CellActionUserActiveComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CellActionUserActiveComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
