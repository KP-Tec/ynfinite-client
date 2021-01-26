const YnfiniteForms = {
  setup() {
    document.addEventListener("DOMContentLoaded", () => {
      const forms = document.querySelectorAll("[data-ynfiniteform=true]");

      forms.forEach((form) => {
        const complexFields = form.querySelectorAll(
          ".widget--complexFormField"
        );

        complexFields.forEach((complexField) => {
          const newAction = complexField.querySelector(
            ".yn-complexForm-actions-new"
          );

          const rowTemplate = complexField.querySelector(
            "#complexField_" + complexField.dataset.ynformuid
          );

          const dataContainer = complexField.querySelector(
            ".yn-complexForm-data"
          );

          newAction.addEventListener("click", (e) => {
            e.preventDefault();
            const newRow = rowTemplate.content.cloneNode(true);
            newRow.className = "yn-complexForm-row";

            const deleteButton = newRow.querySelector(
              ".yn-complexFormActions-delete"
            );

            const fields = newRow.querySelectorAll("[data-ynfield=true]");
            console.log("FIELDS IN ROW", fields);

            fields.forEach((f) => {
              f.setAttribute(
                "name",
                f.name.replace("::count::", dataContainer.childElementCount)
              );
            });

            dataContainer.appendChild(newRow);

            deleteButton.addEventListener("click", (e) => {
              e.preventDefault();
              const row = e.target.closest(".yn-complexForm-row");
              dataContainer.removeChild(row);
            });
          });
        });
      });
    });
  },
};

module.exports = YnfiniteForms;
