const YnfiniteForms = {
  setup() {
    document.addEventListener("DOMContentLoaded", () => {
      const forms = document.querySelectorAll("[data-ynfiniteform=true]");
      console.log("FORMS", forms);

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

          console.log("ROW TEMPLATE", rowTemplate.content);

          const dataContainer = complexField.querySelector(
            ".yn-complexForm-data"
          );

          newAction.addEventListener("click", (e) => {
            e.preventDefault();
            const newRow = rowTemplate.content.cloneNode(true);
            console.log("Created new row", newRow);
            newRow.className = "yn-complexForm-row";

            const deleteButton = newRow.querySelector(
              ".yn-complexFormActions-delete"
            );

            dataContainer.appendChild(newRow);

            console.log("DLETE BUTTON", deleteButton);

            deleteButton.addEventListener("click", (e) => {
              e.preventDefault();
              const row = e.target.closest(".yn-complexForm-row");
              dataContainer.removeChild(row);
            });
          });

          console.log("NEW ACTION", newAction);
        });
      });
    });
  },
};

module.exports = YnfiniteForms;
