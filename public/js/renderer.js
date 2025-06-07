/**
 * 递归渲染任意符合 LINE Flex Message 规范的 JSON，生成对应的 DOM 元素
 * 兼容：
 * - box.layout = "vertical"/"horizontal"/"baseline"，搭配 useBaseline 决定 baseline 或 center 对齐
 * - flex: 0 → "0 0 auto"，保证文字元素不被挤压过窄
 * - text.decoration = "underline"/"line-through"
 * - text.wrap = true/false
 * - text.maxLines = N → 添加多行截断样式
 * - image.size = "full"：等比容器；非 "full"：按固定像素尺寸类 .image-xxs/.image-sm 等
 * - image.aspectRatio = "W:H" → 等比容器（支持常见 1:1、2:3、16:9、20:13；其他自动计算）
 * - image.aspectMode = "cover"/"fit"
 * - image.gravity = "top"/"center"/"bottom"
 * - box/background/image/icon/button/separator/spacer/filler 各自的 margin/flex/position/offset 等
 * - 支持 box.size（nano/micro/kilo/mega），carousel.direction（ltr/rtl）
 * - 支持 box.height = "xxs"/"xs"/"sm"/"md"/"lg"/"xl"/"xxl"，或任意 "<number>px"
 * - 支持 box.width = 任意 "<number>px"
 */
function renderFlexComponent(component, role = "") {
  if (!component || typeof component !== "object" || !component.type) return null;

  let el = null;

  switch (component.type) {
    // ================ 0. carousel ================
    case "carousel": {
      el = document.createElement("div");
      el.classList.add("carousel-container");
      // 支持 direction="rtl"
      if (component.direction) {
        el.setAttribute("direction", component.direction);
      }
      if (Array.isArray(component.contents)) {
        component.contents.forEach((bubbleJson) => {
          // 对 bubble 还可传递 direction
          const bubbleEl = renderFlexComponent(
            { ...bubbleJson, direction: component.direction },
            "bubble"
          );
          if (bubbleEl) {
            el.appendChild(bubbleEl);
          }
        });
      }
      break;
    }

    // ================ 1. bubble =================
    case "bubble": {
      el = document.createElement("div");
      el.classList.add("bubble-container");
      // 支持 size="nano"/"micro"/"kilo"/"mega"
      if (component.size) {
        el.setAttribute("size", component.size);
      }
      // 支持 direction="rtl"
      if (component.direction) {
        el.setAttribute("direction", component.direction);
      }
      // 按官方顺序渲染 header / hero / body / footer
      if (component.header) {
        const headerEl = renderFlexComponent(
          { ...component.header, role: "header" },
          "header"
        );
        if (headerEl) el.appendChild(headerEl);
      }
      if (component.hero) {
        const heroEl = renderFlexComponent(
          { ...component.hero, role: "hero" },
          "hero"
        );
        if (heroEl) el.appendChild(heroEl);
      }
      if (component.body) {
        const bodyEl = renderFlexComponent(
          { ...component.body, role: "body" },
          "body"
        );
        if (bodyEl) el.appendChild(bodyEl);
      }
      if (component.footer) {
        const footerEl = renderFlexComponent(
          { ...component.footer, role: "footer" },
          "footer"
        );
        if (footerEl) el.appendChild(footerEl);
      }
      break;
    }

    // ================ 2. box ===================
    case "box": {
      el = document.createElement("div");
      el.classList.add("flex-box");

      // 2.1. layout 判断：vertical / horizontal / baseline
      if (component.layout === "vertical") {
        el.classList.add("flex-vertical");
      } else if (component.layout === "horizontal") {
        el.classList.add("flex-horizontal");
      } else if (component.layout === "baseline") {
        // 如果 JSON 中写了 useBaseline=true，就用 baseline 对齐
        if (component.useBaseline) {
          el.classList.add("flex-baseline");
        } else {
          // 否则默认改为垂直居中对齐
          el.classList.add("flex-align-center");
        }
      }

      // 2.2. spacing (子元素之间的 gap)
      if (component.spacing) {
        el.classList.add(`spacing-${component.spacing}`);
      }

      // 2.3. margin-top
      if (component.margin) {
        el.classList.add(`margin-${component.margin}`);
      }

      // 2.4. paddingAll
      if (component.paddingAll) {
        const p = component.paddingAll.trim();
        if (p === "0px") {
          el.classList.add("padding-all-none");
        } else if (p === "4px") {
          el.classList.add("padding-all-xs");
        } else if (p === "8px") {
          el.classList.add("padding-all-sm");
        } else if (p === "12px") {
          el.classList.add("padding-all-md");
        } else if (p === "16px") {
          el.classList.add("padding-all-lg");
        } else {
          // 其他任意 px 值时直接内联
          el.style.padding = p;
        }
      }
      // 2.5. paddingTop
      if (component.paddingTop) {
        el.style.paddingTop = component.paddingTop;
      }
      // 2.6. paddingStart (等同于 CSS padding-left)
      if (component.paddingStart) {
        el.style.paddingLeft = component.paddingStart;
      }
      // 2.7. paddingEnd (等同于 CSS padding-right)
      if (component.paddingEnd) {
        el.style.paddingRight = component.paddingEnd;
      }
      // 2.8. paddingBottom
      if (component.paddingBottom) {
        el.style.paddingBottom = component.paddingBottom;
      }

      // 2.9. backgroundColor / borderWidth & borderColor / cornerRadius
      if (component.backgroundColor) {
        el.style.backgroundColor = component.backgroundColor;
      }
      if (component.borderWidth) {
        // 如果同时有 borderColor 再一起设置
        if (component.borderColor) {
          el.style.border = `${component.borderWidth} solid ${component.borderColor}`;
        } else {
          // 只写 borderWidth 也至少保证 border-style 为 solid
          el.style.borderWidth = component.borderWidth;
          el.style.borderStyle = "solid";
        }
      }
      if (component.borderColor && !component.borderWidth) {
        // 仅写 borderColor 时，也可给默认 1px 实线
        el.style.border = `1px solid ${component.borderColor}`;
      }
      if (component.cornerRadius) {
        el.style.borderRadius = component.cornerRadius;
      }

      // 2.10. position:absolute / relative + offset
      if (component.position === "absolute") {
        el.classList.add("position-absolute");
        if (component.offsetTop) {
          el.style.top = component.offsetTop;
        }
        if (component.offsetBottom) {
          el.style.bottom = component.offsetBottom;
        }
        if (component.offsetStart) {
          el.style.left = component.offsetStart;
        }
        if (component.offsetEnd) {
          el.style.right = component.offsetEnd;
        }
      } else if (component.position === "relative") {
        el.classList.add("position-relative");
      }

      // 2.11. 支持 box.height
      if (component.height) {
        // 如果是枚举关键词 xxs/xs/.../xxl，使用 CSS class
        if (
          ["xxs", "xs", "sm", "md", "lg", "xl", "xxl"].includes(component.height)
        ) {
          el.classList.add(`height-${component.height}`);
        } else {
          // 任意 "<n>px" 等，直接用内联样式
          el.style.height = component.height;
        }
      }

      // 2.12. 支持 box.width（任意 "<n>px"）
      if (component.width) {
        el.style.width = component.width;
      }

      // 2.13. flex 属性：若 flex=0 → 0 0 auto；否则直接用数字
      if (component.flex !== undefined) {
        if (component.flex === 0) {
          el.style.flex = "0 0 auto";
        } else {
          el.style.flex = component.flex;
        }
      }

      // 2.14. 如果非绝对定位且出现 offsetTop/offsetBottom/offsetStart/offsetEnd，
      //      用 position: relative + 对应 top/bottom/left/right
      if (component.position !== "absolute") {
        let hasRelativeOffset = false;
        if (component.offsetTop) {
          el.style.position = "relative";
          el.style.top = component.offsetTop;
          hasRelativeOffset = true;
        }
        if (component.offsetBottom) {
          el.style.position = "relative";
          el.style.bottom = component.offsetBottom;
          hasRelativeOffset = true;
        }
        if (component.offsetStart) {
          el.style.position = "relative";
          el.style.left = component.offsetStart;
          hasRelativeOffset = true;
        }
        if (component.offsetEnd) {
          el.style.position = "relative";
          el.style.right = component.offsetEnd;
          hasRelativeOffset = true;
        }
        // 如果没有任何 offset，就不强制覆盖已有 position
        if (hasRelativeOffset && component.position !== "relative") {
          // 若之前未显式设置 position:relative，则留意不覆盖已做的 absolute
          // 这里只确保 offset 能生效
        }
      }

      // 2.15. action：如果 type="uri"，把整个 el 包裹在 <a> 里；其他类型仅做简单 console.log
      if (component.action && component.action.type === "uri") {
        const link = document.createElement("a");
        link.href = component.action.uri;
        link.target = "_blank";
        // 把后续渲染都挂到 link 里
        el.appendChild(link);
        // 用 el = link 继续挂载内部内容
        el = link;
      }
      // postback/message 等类型仅做占位
      else if (
        component.action &&
        (component.action.type === "postback" || component.action.type === "message")
      ) {
        el.style.cursor = "pointer";
        el.addEventListener("click", () => {
          if (component.action.type === "postback") {
            console.log("Postback data:", component.action.data);
          } else {
            console.log("Send message text:", component.action.text);
          }
        });
      }

      // 2.16. 如果 role 为 body/header/footer，需要额外加 class
      if (role === "body") {
        el.classList.add("bubble-body");
      }
      if (role === "header") {
        el.classList.add("bubble-header");
      }
      if (role === "footer") {
        el.classList.add("bubble-footer");
      }

      // 2.17. 递归渲染 contents
      if (Array.isArray(component.contents)) {
        component.contents.forEach((child) => {
          // 标记 filler 与 icon 的角色，便于后续处理
          if (child.type === "filler") child.role = "filler";
          if (child.type === "icon") child.role = "icon";
          const childEl = renderFlexComponent(child, "");
          if (childEl) {
            el.appendChild(childEl);
          }
        });
      }

      break;
    }

    // ================ 3. text ===================
    case "text": {
      el = document.createElement("div");
      el.classList.add("text-content");
      el.textContent = component.text || "";

      // 3.1. size
      if (component.size) {
        switch (component.size) {
          case "xs":
            el.classList.add("text-xs");
            break;
          case "sm":
            el.classList.add("text-sm");
            break;
          case "md":
            el.classList.add("text-md");
            break;
          case "lg":
            el.classList.add("text-lg");
            break;
          case "xl":
            el.classList.add("text-xl");
            break;
          case "xxl":
            el.classList.add("text-xxl");
            break;
        }
      }

      // 3.2. weight
      if (component.weight === "bold") {
        el.classList.add("text-bold");
      } else {
        el.classList.add("text-regular");
      }

      // 3.3. color
      if (component.color) {
        el.style.color = component.color;
      }

      // 3.4. align
      if (component.align) {
        switch (component.align) {
          case "start":
            el.classList.add("align-start");
            break;
          case "center":
            el.classList.add("align-center");
            break;
          case "end":
            el.classList.add("align-end");
            break;
        }
      }

      // 3.5. wrap / nowrap
      if (component.wrap === true) {
        el.classList.add("wrap");
      } else if (component.wrap === false) {
        el.classList.add("nowrap");
      }

      // 3.6. decoration
      if (component.decoration === "underline") {
        el.classList.add("underline");
      } else if (component.decoration === "line-through") {
        el.classList.add("line-through");
      }

      // 3.7. offsetTop/offsetBottom/offsetStart/offsetEnd
      //       使用 position: relative + top/bottom/left/right
      if (component.offsetTop) {
        el.style.position = "relative";
        el.style.top = component.offsetTop;
      }
      if (component.offsetBottom) {
        el.style.position = "relative";
        el.style.bottom = component.offsetBottom;
      }
      if (component.offsetStart) {
        el.style.position = "relative";
        el.style.left = component.offsetStart;
      }
      if (component.offsetEnd) {
        el.style.position = "relative";
        el.style.right = component.offsetEnd;
      }

      // 3.8. flex 属性处理：flex=0 → 0 0 auto
      if (component.flex !== undefined) {
        if (component.flex === 0) {
          el.style.flex = "0 0 auto";
        } else {
          el.style.flex = component.flex;
        }
      }

      // 3.9. margin-top
      if (component.margin) {
        el.classList.add(`margin-${component.margin}`);
      }

      // 3.10. maxLines 行数限制
      if (component.maxLines !== undefined) {
        el.classList.add(`max-lines-${component.maxLines}`);
      }

      // 3.11. gravity（可选，如果想支持，可用 alignSelf）
      if (component.gravity === "bottom") {
        el.style.alignSelf = "flex-end";
      } else if (component.gravity === "center") {
        el.style.alignSelf = "center";
      } else if (component.gravity === "top") {
        el.style.alignSelf = "flex-start";
      }

      // 3.12. action: 如果 text 本身可点，可类似按钮做 onclick
      if (component.action && component.action.type === "uri") {
        el.style.cursor = "pointer";
        el.addEventListener("click", () => {
          window.open(component.action.uri, "_blank");
        });
      } else if (
        component.action &&
        (component.action.type === "postback" || component.action.type === "message")
      ) {
        el.style.cursor = "pointer";
        el.addEventListener("click", () => {
          if (component.action.type === "postback") {
            console.log("Text postback data:", component.action.data);
          } else {
            console.log("Text send message:", component.action.text);
          }
        });
      }

      break;
    }

    // ================ 4. image ==================
    case "image": {
      // 包裹 <img> 的外层容器
      const wrapper = document.createElement("div");

      // 4.1. 支持 size="full" 时创建等比容器
      if (component.size === "full" && component.aspectRatio) {
        const ratioParts = component.aspectRatio.split(":").map(Number);
        if (
          ratioParts.length === 2 &&
          Number.isFinite(ratioParts[0]) &&
          Number.isFinite(ratioParts[1]) &&
          ratioParts[0] > 0 &&
          ratioParts[1] > 0
        ) {
          const [w, h] = ratioParts;
          if (w === 2 && h === 3) {
            wrapper.classList.add("aspect-2-3");
          } else if (w === 1 && h === 1) {
            wrapper.classList.add("aspect-1-1");
          } else if (w === 16 && h === 9) {
            wrapper.classList.add("aspect-16-9");
          } else if (w === 20 && h === 13) {
            wrapper.classList.add("aspect-20-13");
          } else {
            // 其他比例，动态计算 padding-bottom
            const percent = (h / w) * 100;
            wrapper.style.position = "relative";
            wrapper.style.width = "100%";
            wrapper.style.paddingBottom = `${percent}%`;
            wrapper.style.overflow = "hidden";
          }
        }
      }
      // 4.2. 支持 image.size 非 "full" 时，加上 .image-xxx 类
      else if (component.size && component.size !== "full") {
        wrapper.classList.add(`image-${component.size}`);
      }

      // 4.3. <img> 本体
      const img = document.createElement("img");
      const defaultImageUrl = "../assets/admin/img/chenibg01.jpg"; // 請修改為您專案中實際的預設圖片路徑
      img.src = component.url && isValidUrl(component.url) ? component.url : defaultImageUrl;
      img.alt = component.alt || "";

      // 4.4. aspectMode
      if (component.aspectMode === "cover") {
        img.classList.add("hero-image");
      } else {
        img.style.objectFit = "contain";
        img.style.width = "100%";
        img.style.height = "100%";
      }

      // 4.5. gravity
      if (component.gravity === "top") {
        img.classList.add("gravity-top");
      } else if (component.gravity === "center") {
        img.classList.add("gravity-center");
      } else if (component.gravity === "bottom") {
        img.classList.add("gravity-bottom");
      }

      // 4.6. backgroundColor / cornerRadius
      if (component.backgroundColor) {
        wrapper.style.backgroundColor = component.backgroundColor;
      }
      if (component.cornerRadius) {
        wrapper.style.borderRadius = component.cornerRadius;
      }

      // 4.7. position:absolute + offset
      if (component.position === "absolute") {
        wrapper.classList.add("position-absolute");
        if (component.offsetTop) {
          wrapper.style.top = component.offsetTop;
        }
        if (component.offsetBottom) {
          wrapper.style.bottom = component.offsetBottom;
        }
        if (component.offsetStart) {
          wrapper.style.left = component.offsetStart;
        }
        if (component.offsetEnd) {
          wrapper.style.right = component.offsetEnd;
        }
      } else if (component.position === "relative") {
        wrapper.classList.add("position-relative");
      }

      // 4.8. 非绝对定位时的 offset
      if (component.position !== "absolute") {
        if (component.offsetTop) {
          wrapper.style.position = "relative";
          wrapper.style.top = component.offsetTop;
        }
        if (component.offsetBottom) {
          wrapper.style.position = "relative";
          wrapper.style.bottom = component.offsetBottom;
        }
        if (component.offsetStart) {
          wrapper.style.position = "relative";
          wrapper.style.left = component.offsetStart;
        }
        if (component.offsetEnd) {
          wrapper.style.position = "relative";
          wrapper.style.right = component.offsetEnd;
        }
      }

      // 4.9. flex 属性
      if (component.flex !== undefined) {
        if (component.flex === 0) {
          wrapper.style.flex = "0 0 auto";
        } else {
          wrapper.style.flex = component.flex;
        }
      }

      // 4.10. action: uri/postback/message
      if (component.action && component.action.type === "uri") {
        const link = document.createElement("a");
        link.href = component.action.uri;
        link.target = "_blank";
        link.appendChild(img);
        wrapper.appendChild(link);
      } else if (
        component.action &&
        (component.action.type === "postback" || component.action.type === "message")
      ) {
        img.style.cursor = "pointer";
        img.addEventListener("click", () => {
          if (component.action.type === "postback") {
            console.log("Image postback data:", component.action.data);
          } else {
            console.log("Image send message:", component.action.text);
          }
        });
        wrapper.appendChild(img);
      } else {
        wrapper.appendChild(img);
      }

      el = wrapper;
      break;
    }

    // ================ 5. icon ===================
    case "icon": {
      const wrapper = document.createElement("div");
      wrapper.classList.add("icon-box");

      const img = document.createElement("img");
      const defaultImageUrl = "../assets/admin/img/ci.png"; // 請修改為您專案中實際的預設圖片路徑
      img.src = component.url && isValidUrl(component.url) ? component.url : defaultImageUrl;
      img.alt = component.alt || "";

      // 5.1. size：xxs/xs/sm/md/lg
      const size = component.size || "md";
      switch (size) {
        case "xxs":
          img.classList.add("icon-xxs");
          break;
        case "xs":
          img.classList.add("icon-xs");
          break;
        case "sm":
          img.classList.add("icon-sm");
          break;
        case "md":
          img.classList.add("icon-md");
          break;
        case "lg":
          img.classList.add("icon-lg");
          break;
      }

      // 5.2. margin-top 等可选属性
      if (component.margin) {
        wrapper.classList.add(`margin-${component.margin}`);
      }
      // 5.3. offsetTop/offsetBottom/offsetStart/offsetEnd
      //      使用 position: relative + top/bottom/left/right
      if (component.offsetTop) {
        wrapper.style.position = "relative";
        wrapper.style.top = component.offsetTop;
      }
      if (component.offsetBottom) {
        wrapper.style.position = "relative";
        wrapper.style.bottom = component.offsetBottom;
      }
      if (component.offsetStart) {
        wrapper.style.position = "relative";
        wrapper.style.left = component.offsetStart;
      }
      if (component.offsetEnd) {
        wrapper.style.position = "relative";
        wrapper.style.right = component.offsetEnd;
      }

      // 5.4. flex
      if (component.flex !== undefined) {
        if (component.flex === 0) {
          wrapper.style.flex = "0 0 auto";
        } else {
          wrapper.style.flex = component.flex;
        }
      }

      // 5.5. position / offset (absolute 的情况)
      if (component.position === "absolute") {
        wrapper.classList.add("position-absolute");
        if (component.offsetTop) {
          wrapper.style.top = component.offsetTop;
        }
        if (component.offsetBottom) {
          wrapper.style.bottom = component.offsetBottom;
        }
        if (component.offsetStart) {
          wrapper.style.left = component.offsetStart;
        }
        if (component.offsetEnd) {
          wrapper.style.right = component.offsetEnd;
        }
      } else if (component.position === "relative") {
        wrapper.classList.add("position-relative");
      }

      // 5.6. action: uri/postback/message
      if (component.action && component.action.type === "uri") {
        wrapper.style.cursor = "pointer";
        wrapper.addEventListener("click", () => {
          window.open(component.action.uri, "_blank");
        });
      } else if (
        component.action &&
        (component.action.type === "postback" || component.action.type === "message")
      ) {
        wrapper.style.cursor = "pointer";
        wrapper.addEventListener("click", () => {
          if (component.action.type === "postback") {
            console.log("Icon postback:", component.action.data);
          } else {
            console.log("Icon send message:", component.action.text);
          }
        });
      }

      wrapper.appendChild(img);
      el = wrapper;
      break;
    }

    // ================ 6. filler =================
    case "filler": {
      el = document.createElement("div");
      el.classList.add("filler"); // CSS: .filler { flex: 1; }
      // 6.1. flex
      if (component.flex !== undefined) {
        el.style.flex = component.flex;
      }
      break;
    }

    // ================ 7. button =================
    case "button": {
      const btn = document.createElement("button");
      const act = component.action || {};

      // 7.1. style: link / primary / secondary
      if (component.style === "link") {
        btn.classList.add("btn-link");
      } else if (component.style === "primary") {
        btn.classList.add("btn-primary");
      } else if (component.style === "secondary") {
        btn.classList.add("btn-secondary");
      }

      // 7.2. 文本内容
      btn.textContent = act.label || "";

      // 7.3. color（仅 primary/secondary 生效）
      if (component.color && component.style !== "link") {
        btn.style.color = component.color;
      }

      // 7.4. height: sm/md/lg
      if (component.height) {
        switch (component.height) {
          case "sm":
            btn.classList.add("btn-sm");
            break;
          case "md":
            btn.classList.add("btn-md");
            break;
          case "lg":
            btn.classList.add("btn-lg");
            break;
        }
      }

      // 7.5. flex 属性：0 → 0 0 auto
      if (component.flex !== undefined) {
        if (component.flex === 0) {
          btn.style.flex = "0 0 auto";
        } else {
          btn.style.flex = component.flex;
        }
      }

      // 7.6. margin-top
      if (component.margin) {
        btn.classList.add(`margin-${component.margin}`);
      }

      // 7.7. action: uri/postback/message
      if (act.type === "uri" && act.uri) {
        btn.addEventListener("click", () => {
          window.open(act.uri, "_blank");
        });
      } else if (act.type === "postback") {
        btn.addEventListener("click", () => {
          console.log("Button postback:", act.data);
        });
      } else if (act.type === "message") {
        btn.addEventListener("click", () => {
          console.log("Button send message:", act.text);
        });
      }

      el = btn;
      break;
    }

    // ================ 8. separator ================
    case "separator": {
      el = document.createElement("div");
      el.classList.add("separator");
      // 8.1. color
      if (component.color) {
        el.style.backgroundColor = component.color;
      }
      // 8.2. margin-top
      if (component.margin) {
        el.classList.add(`margin-${component.margin}`);
      }
      // 8.3. flex 属性
      if (component.flex !== undefined) {
        el.style.flex = component.flex;
      }
      break;
    }

    // ================ 9. spacer ===================
    case "spacer": {
      el = document.createElement("div");
      el.classList.add("spacer");
      if (component.size) {
        el.classList.add(`spacer-${component.size}`);
      }
      if (component.flex !== undefined) {
        el.style.flex = component.flex;
      }
      if (component.margin) {
        el.classList.add(`margin-${component.margin}`);
      }
      // position / offset 可按需增加（如上面其它 type 演示）
      break;
    }

    // ========== 10. 其他不支持 type =============
    default:
      el = null;
      break;
  }

  return el;
}

// 檢查 URL 是否有效
function isValidUrl(string) {
  try {
    new URL(string);
    return true;
  } catch (_) {
    return false;
  }
}

/**
 * 页面加载后，将 flexJson 渲染到 #flex-root
 */
// document.addEventListener("DOMContentLoaded", () => {
//   // 使用你最新提供的 JSON 示例，确保 footer 正常显示
//   const flexJson = {
//     type: "carousel",
//     direction: "ltr", // 可选 ltr/rtl
//     contents: [
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://developers-resource.landpress.line.me/fx/img/01_1_cafe.png",
//           size: "full",
//           aspectRatio: "20:13",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "Brown Cafe",
//               weight: "bold",
//               size: "xl",
//             },
//             {
//               type: "box",
//               layout: "baseline",
//               margin: "md",
//               contents: [
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gray_star_28.png",
//                 },
//                 {
//                   type: "text",
//                   text: "4.0",
//                   size: "sm",
//                   color: "#999999",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "lg",
//               spacing: "sm",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   spacing: "sm",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Place",
//                       color: "#aaaaaa",
//                       size: "sm",
//                       flex: 1,
//                     },
//                     {
//                       type: "text",
//                       text: "Flex Tower, 7-7-4 Midori-ku, Tokyo",
//                       wrap: true,
//                       color: "#666666",
//                       size: "sm",
//                       flex: 5,
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   spacing: "sm",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Time",
//                       color: "#aaaaaa",
//                       size: "sm",
//                       flex: 1,
//                     },
//                     {
//                       type: "text",
//                       text: "10:00 - 23:00",
//                       wrap: true,
//                       color: "#666666",
//                       size: "sm",
//                       flex: 5,
//                     },
//                   ],
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "button",
//               style: "link",
//               height: "sm",
//               action: {
//                 type: "uri",
//                 label: "CALL",
//                 uri: "https://line.me/",
//               },
//             },
//             {
//               type: "button",
//               style: "link",
//               height: "sm",
//               action: {
//                 type: "uri",
//                 label: "WEBSITE",
//                 uri: "https://line.me/",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "image",
//               url: "https://developers-resource.landpress.line.me/fx/clip/clip1.jpg",
//               size: "full",
//               aspectMode: "cover",
//               aspectRatio: "2:3",
//               gravity: "top",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Brown's T-shirts",
//                       size: "xl",
//                       color: "#ffffff",
//                       weight: "bold",
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "¥35,800",
//                       color: "#ebebeb",
//                       size: "sm",
//                       flex: 0,
//                     },
//                     {
//                       type: "text",
//                       text: "¥75,000",
//                       color: "#ffffffcc",
//                       decoration: "line-through",
//                       gravity: "bottom",
//                       flex: 0,
//                       size: "sm",
//                     },
//                   ],
//                   spacing: "lg",
//                 },
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "filler",
//                     },
//                     {
//                       type: "box",
//                       layout: "baseline",
//                       contents: [
//                         {
//                           type: "filler",
//                         },
//                         {
//                           type: "icon",
//                           url: "https://developers-resource.landpress.line.me/fx/clip/clip14.png",
//                         },
//                         {
//                           type: "text",
//                           text: "Add to cart",
//                           color: "#ffffff",
//                           flex: 0,
//                           offsetTop: "-2px",
//                         },
//                         {
//                           type: "filler",
//                         },
//                       ],
//                       spacing: "sm",
//                     },
//                     {
//                       type: "filler",
//                     },
//                   ],
//                   borderWidth: "1px",
//                   cornerRadius: "4px",
//                   spacing: "sm",
//                   borderColor: "#ffffff",
//                   margin: "xxl",
//                   height: "40px", // 任意 “40px”
//                 },
//               ],
//               position: "absolute",
//               offsetBottom: "0px",
//               offsetStart: "0px",
//               offsetEnd: "0px",
//               backgroundColor: "#03303Acc",
//               paddingAll: "20px",
//               paddingTop: "18px",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "SALE",
//                   color: "#ffffff",
//                   align: "center",
//                   size: "xs",
//                   offsetTop: "3px", // 现在会用 position:relative + top:3px
//                 },
//               ],
//               position: "absolute",
//               cornerRadius: "20px",
//               offsetTop: "18px",
//               backgroundColor: "#ff334b",
//               offsetStart: "18px",
//               height: "25px", // 任意 “25px”
//               width: "53px", // 任意 “53px”
//             },
//           ],
//           paddingAll: "0px",
//         },
//       },
//       {
//         type: "bubble",
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "image",
//               url: "https://developers-resource.landpress.line.me/fx/clip/clip2.jpg",
//               size: "full",
//               aspectMode: "cover",
//               aspectRatio: "2:3",
//               gravity: "top",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Cony's T-shirts",
//                       size: "xl",
//                       color: "#ffffff",
//                       weight: "bold",
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "¥35,800",
//                       color: "#ebebeb",
//                       size: "sm",
//                       flex: 0,
//                     },
//                     {
//                       type: "text",
//                       text: "¥75,000",
//                       color: "#ffffffcc",
//                       decoration: "line-through",
//                       gravity: "bottom",
//                       flex: 0,
//                       size: "sm",
//                     },
//                   ],
//                   spacing: "lg",
//                 },
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "filler",
//                     },
//                     {
//                       type: "box",
//                       layout: "baseline",
//                       contents: [
//                         {
//                           type: "filler",
//                         },
//                         {
//                           type: "icon",
//                           url: "https://developers-resource.landpress.line.me/fx/clip/clip14.png",
//                         },
//                         {
//                           type: "text",
//                           text: "Add to cart",
//                           color: "#ffffff",
//                           flex: 0,
//                           offsetTop: "-2px",
//                         },
//                         {
//                           type: "filler",
//                         },
//                       ],
//                       spacing: "sm",
//                     },
//                     {
//                       type: "filler",
//                     },
//                   ],
//                   borderWidth: "1px",
//                   cornerRadius: "4px",
//                   spacing: "sm",
//                   borderColor: "#ffffff",
//                   margin: "xxl",
//                   height: "40px", // 任意 “40px”
//                 },
//               ],
//               position: "absolute",
//               offsetBottom: "0px",
//               offsetStart: "0px",
//               offsetEnd: "0px",
//               backgroundColor: "#9C8E7Ecc",
//               paddingAll: "20px",
//               paddingTop: "18px",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "SALE",
//                   color: "#ffffff",
//                   align: "center",
//                   size: "xs",
//                   offsetTop: "3px", // 同样会用 position:relative + top:3px
//                 },
//               ],
//               position: "absolute",
//               cornerRadius: "20px",
//               offsetTop: "18px",
//               backgroundColor: "#ff334b",
//               offsetStart: "18px",
//               height: "25px", // 任意 “25px”
//               width: "53px", // 任意 “53px”
//             },
//           ],
//           paddingAll: "0px",
//         },
//       },
//     ],
//   };

//   const root = document.getElementById("flex-root");
//   const rendered = renderFlexComponent(flexJson, "");
//   if (rendered) {
//     root.appendChild(rendered);
//   }
// });
