package cloud.ambar.creditcard.product.event;

import cloud.ambar.common.event.Event;
import lombok.Getter;
import lombok.experimental.SuperBuilder;

@SuperBuilder
@Getter
public class ProductDeactivated extends Event {}
