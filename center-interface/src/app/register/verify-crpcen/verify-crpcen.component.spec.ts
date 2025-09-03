import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VerifyCrpcenComponent } from './verify-crpcen.component';

describe('VerifyCrpcenComponent', () => {
  let component: VerifyCrpcenComponent;
  let fixture: ComponentFixture<VerifyCrpcenComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VerifyCrpcenComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(VerifyCrpcenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
