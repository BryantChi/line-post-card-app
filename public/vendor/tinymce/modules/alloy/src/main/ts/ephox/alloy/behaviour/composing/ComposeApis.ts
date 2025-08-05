import { Optional } from '@ephox/katamari';

import { AlloyComponent } from '../../api/component/ComponentApi';
import { Stateless } from '../common/BehaviourState';
import { ComposingConfig } from './ComposingTypes';

const getCurrent = (component: AlloyComponent, composeConfig: ComposingConfig, _composeState: Stateless): Optional<AlloyComponent> => composeConfig.find(component);

export {
  getCurrent
};
