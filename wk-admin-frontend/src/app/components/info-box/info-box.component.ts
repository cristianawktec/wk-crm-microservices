import { ChangeDetectionStrategy, Component, Input } from '@angular/core';

@Component({
  selector: 'app-info-box',
  templateUrl: './info-box.component.html',
  styleUrls: ['./info-box.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class InfoBoxComponent {
  @Input() title: string = '';
  @Input() value: string | number = '';
  @Input() icon: string = 'fas fa-info-circle';
  @Input() color: 'info' | 'success' | 'warning' | 'danger' | 'primary' = 'info';
  @Input() subtitle?: string;
  @Input() progressPercent?: number; // 0-100 optional
}
