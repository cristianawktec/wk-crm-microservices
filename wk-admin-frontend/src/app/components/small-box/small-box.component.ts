import { ChangeDetectionStrategy, Component, Input } from '@angular/core';

@Component({
  selector: 'app-small-box',
  templateUrl: './small-box.component.html',
  styleUrls: ['./small-box.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SmallBoxComponent {
  @Input() title: string = '';
  @Input() value: string | number | null = '';
  @Input() icon: string = 'fas fa-info-circle';
  @Input() color: 'info' | 'warning' | 'success' | 'danger' = 'info';
}
